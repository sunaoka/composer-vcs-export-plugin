<?php

declare(strict_types=1);

namespace Tests;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\BufferIO;
use Composer\IO\NullIO;
use Composer\Package\CompletePackage;
use Composer\Repository\ArrayRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Sunaoka\Composer\Vcs\Export\Plugin;
use Tests\Mocks\Filesystem;
use Tests\Mocks\ProcessExecutor;
use Tests\Mocks\UnknownOperation;

class PluginTest extends TestCase
{
    /**
     * @return array<array{Filesystem, ProcessExecutor, string}>
     */
    public static function packageProvider(): array
    {
        return [
            [
                new Filesystem(true),
                new ProcessExecutor([0, 0]),
                '- Archiving vendor-name/package-name (1.0.0): Extracting archive',
            ],
            [
                new Filesystem(false),
                new ProcessExecutor([0, 0]),
                '',
            ],
            [
                new Filesystem(true),
                new ProcessExecutor([1, 0]),
                '- Archiving vendor-name/package-name (1.0.0): Failed to archive',
            ],
            [
                new Filesystem(true),
                new ProcessExecutor([0, 1]),
                '- Archiving vendor-name/package-name (1.0.0): Failed to extract',
            ],
        ];
    }

    /**
     * @dataProvider packageProvider
     */
    #[DataProvider('packageProvider')]
    public function testOnPostPackageInstall(Filesystem $filesystem, ProcessExecutor $processExecutor, string $expected): void
    {
        $package = new CompletePackage('vendor-name/package-name', '1.0.0.0', '1.0.0');
        $package->setSourceType('git');

        $event = new PackageEvent(
            PackageEvents::POST_PACKAGE_INSTALL,
            $this->composer,
            new NullIO(),
            true,
            new ArrayRepository(),
            [new InstallOperation($package)],
            new InstallOperation($package)
        );

        $io = new BufferIO();
        $plugin = new Plugin($filesystem, $processExecutor);
        $plugin->activate($this->composer, $io);
        $plugin->onPostPackageInstall($event);

        self::assertSame($expected, trim($io->getOutput()));

        $plugin->deactivate($this->composer, $io);
        $plugin->uninstall($this->composer, $io);
    }

    /**
     * @dataProvider packageProvider
     */
    #[DataProvider('packageProvider')]
    public function testOnPostPackageUpdate(Filesystem $filesystem, ProcessExecutor $processExecutor, string $expected): void
    {
        $initial = new CompletePackage('vendor-name/package-name', '0.9.0.0', '0.9.0');
        $initial->setSourceType('git');

        $target = new CompletePackage('vendor-name/package-name', '1.0.0.0', '1.0.0');
        $target->setSourceType('git');

        $event = new PackageEvent(
            PackageEvents::POST_PACKAGE_UPDATE,
            $this->composer,
            new NullIO(),
            true,
            new ArrayRepository(),
            [new UpdateOperation($initial, $target)],
            new UpdateOperation($initial, $target)
        );

        $io = new BufferIO();
        $plugin = new Plugin($filesystem, $processExecutor);
        $plugin->activate($this->composer, $io);
        $plugin->onPostPackageUpdate($event);

        self::assertSame($expected, trim($io->getOutput()));

        $plugin->deactivate($this->composer, $io);
        $plugin->uninstall($this->composer, $io);
    }

    public function testNeitherInstallOperationNorInstallOperation(): void
    {
        $event = new PackageEvent(
            PackageEvents::POST_PACKAGE_INSTALL,
            $this->composer,
            new NullIO(),
            true,
            new ArrayRepository(),
            [new UnknownOperation()],
            new UnknownOperation()
        );

        $io = new BufferIO();
        $plugin = new Plugin();
        $plugin->activate($this->composer, $io);
        $plugin->onPostPackageInstall($event);

        self::assertSame('', trim($io->getOutput()));
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertCount(2, Plugin::getSubscribedEvents());
    }
}
