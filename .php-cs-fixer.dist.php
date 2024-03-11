<?php

declare(strict_types=1);

use PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->notName('/sandbox/i');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->registerCustomFixers(new PhpCsFixerCustomFixers\Fixers())
    ->setRules([
        '@Symfony' => true, // should be last of rules set to avoid override its rules
        // here we override some rules
        'yoda_style' => false,
        'declare_strict_types' => true,
        'class_definition' => [
            'single_line' => false,
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_to_comment' => [
            'ignored_tags' => [
                'todo',
                'uses',
            ],
        ],
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => [
                'arguments',
                'arrays',
                'match',
                'parameters',
            ],
        ],
        // https://github.com/kubawerlos/php-cs-fixer-custom-fixers#multilinepromotedpropertiesfixer
        MultilinePromotedPropertiesFixer::name() => [
            'keep_blank_lines' => true,
        ],
    ])
    ->setFinder($finder);
