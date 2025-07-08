<?php

$dirs = [
    __DIR__.'/src',
    __DIR__.'/tests',
];

$rules = [
    '@Symfony' => true,
];

return (new PhpCsFixer\Config())
    ->setRules($rules)
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setFinder(PhpCsFixer\Finder::create()->in($dirs)->append([__FILE__]));
