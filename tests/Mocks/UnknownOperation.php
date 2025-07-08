<?php

declare(strict_types=1);

namespace Tests\Mocks;

use Composer\DependencyResolver\Operation\SolverOperation;

class UnknownOperation extends SolverOperation
{
    public function show(bool $lock): string
    {
        return 'Unknown';
    }
}
