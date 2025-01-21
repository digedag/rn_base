<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('Resources')
    ->exclude('Documentation')
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();
return $config->setFinder($finder)
    ->setCacheFile('.Build/.php_cs.cache')
    ->setRules([
        '@Symfony' => true,
        'phpdoc_align' => false,
        'no_superfluous_phpdoc_tags' => false,
        'global_namespace_import' => [
            'import_classes' => true, 'import_constants' => false, 'import_functions' => false
        ],
        'phpdoc_separation' => [
            'skip_unlisted_annotations' => true,
        ],
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            // no support for "arguments" and "parameters" as we need support for PHP 7.4
            'elements' => [
                'array_destructuring',
                'arrays',
                'match',
            ],
        ],
    ])
    ->setLineEnding("\n")
;
