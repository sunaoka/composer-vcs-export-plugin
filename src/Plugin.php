<?php

declare(strict_types=1);

namespace Sunaoka\Composer\Vcs\Export;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ProcessExecutor
     */
    protected $process;

    public function __construct(?Filesystem $filesystem = null, ?ProcessExecutor $process = null)
    {
        $this->filesystem = $filesystem ?? new Filesystem();
        $this->process = $process ?? new ProcessExecutor();
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    /**
     * @uses onPostPackageInstall
     * @uses onPostPackageUpdate
     */
    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => ['onPostPackageInstall'],
            PackageEvents::POST_PACKAGE_UPDATE => ['onPostPackageUpdate'],
        ];
    }

    public function onPostPackageInstall(PackageEvent $event): void
    {
        $this->handlePackageEvent($event);
    }

    public function onPostPackageUpdate(PackageEvent $event): void
    {
        $this->handlePackageEvent($event);
    }

    protected function getPackage(PackageEvent $event): ?PackageInterface
    {
        $operation = $event->getOperation();

        if ($operation instanceof InstallOperation) {
            return $operation->getPackage();
        }

        if ($operation instanceof UpdateOperation) {
            return $operation->getTargetPackage();
        }

        return null;
    }

    protected function handlePackageEvent(PackageEvent $event): void
    {
        $package = $this->getPackage($event);
        if (null === $package) {
            return;
        }

        if ('git' === $package->getSourceType()) {
            /** @var string $installPath The installation path is not null because it is executed by PackageEvents::POST_PACKAGE_INSTALL or PackageEvents::POST_PACKAGE_UPDATE */
            $installPath = $this->composer->getInstallationManager()->getInstallPath($package);
            $this->replaceWithGitArchive($package, $installPath);
        }
    }

    protected function replaceWithGitArchive(PackageInterface $package, string $installPath): void
    {
        if (!$this->filesystem->exists(["{$installPath}/.git", "{$installPath}/.gitattributes"])) {
            return;
        }

        $this->io->writeError("  - Archiving <info>{$package->getPrettyName()}</info> (<comment>{$package->getFullPrettyVersion()}</comment>): ", false);

        $archive = $this->filesystem->tempnam(sys_get_temp_dir(), 'composer-vcs-export-plugin-');

        try {
            $command = ['git', 'archive', '--format=zip', '-0', '-o', $archive, 'HEAD'];
            if (0 !== $this->process->execute($command, $output, $installPath)) {
                throw new \RuntimeException('Failed to archive');
            }

            $this->filesystem->remove($installPath);
            $this->filesystem->mkdir($installPath);

            $command = ['unzip', '-q', $archive, '-d', $installPath];
            if (0 !== $this->process->execute($command, $output)) {
                throw new \RuntimeException('Failed to extract');
            }

            $this->io->writeError('Extracting archive');
        } catch (\Throwable $e) {
            $this->io->writeError("<error>{$e->getMessage()}</error>");
        } finally {
            $this->filesystem->remove($archive);
        }
    }
}
