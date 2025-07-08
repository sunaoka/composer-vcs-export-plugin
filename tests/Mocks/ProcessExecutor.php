<?php

declare(strict_types=1);

namespace Tests\Mocks;

use Composer\IO\IOInterface;

class ProcessExecutor extends \Composer\Util\ProcessExecutor
{
    /**
     * @var int[]
     */
    private $status;

    /**
     * @param int[] $status
     */
    public function __construct(array $status = [], ?IOInterface $io = null)
    {
        parent::__construct($io);
        $this->status = $status;
    }

    public function execute($command, &$output = null, ?string $cwd = null): int
    {
        return array_shift($this->status) ?? 0;
    }
}
