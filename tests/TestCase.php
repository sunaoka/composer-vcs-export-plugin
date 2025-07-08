<?php

declare(strict_types=1);

namespace Tests;

use Composer\Composer;
use Composer\Config;
use Composer\Installer\InstallationManager;
use Composer\Installer\MetapackageInstaller;
use Composer\Installer\NoopInstaller;
use Composer\IO\NullIO;
use Composer\Util\HttpDownloader;
use Composer\Util\Loop;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Composer
     */
    protected $composer;

    protected function setUp(): void
    {
        $httpDownloader = new HttpDownloader(new NullIO(), new Config());
        $loop = new Loop($httpDownloader);
        $im = new InstallationManager($loop, new NullIO());
        $im->addInstaller(new NoopInstaller());
        $im->addInstaller(new MetapackageInstaller(new NullIO()));

        $this->composer = new Composer();
        $this->composer->setConfig(new Config());
        $this->composer->setInstallationManager($im);
    }
}
