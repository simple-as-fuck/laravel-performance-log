<?php

declare(strict_types=1);

return (new \PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@PSR1' => true,
    ])
    ->setFinder(
        \PhpCsFixer\Finder::create()
            ->in(__DIR__.'/config')
            ->in(__DIR__.'/src')
    )
    ->setUsingCache(false)
;