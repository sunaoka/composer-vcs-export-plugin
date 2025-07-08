<?php

declare(strict_types=1);

namespace Tests\Mocks;

class Filesystem extends \Symfony\Component\Filesystem\Filesystem
{
    /**
     * @var bool
     */
    private $exists;

    public function __construct(bool $exists = true)
    {
        $this->exists = $exists;
    }

    /**
     * @param string|iterable<string> $files
     */
    public function exists($files): bool
    {
        return $this->exists;
    }

    /**
     * @param string|iterable<string> $files
     */
    public function remove($files): void
    {
    }

    /**
     * @param string|iterable<string> $dirs
     */
    public function mkdir($dirs, int $mode = 0777): void
    {
    }
}
