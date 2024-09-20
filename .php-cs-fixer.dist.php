<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: CC0-1.0

/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:3.58.1|configurator
 * you can change this configuration by importing this file.
 */
$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@Symfony' => true,
        '@PER-CS' => true,
        'function_declaration' => ['closure_fn_spacing' => 'one'],
        'global_namespace_import' => true,
        'multiline_whitespace_before_semicolons' => true,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
        ],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in([
                'src',
            ])
            ->ignoreDotFiles(false)
            ->exclude([
                'node_modules',
                'vendor',
            ])
    );
