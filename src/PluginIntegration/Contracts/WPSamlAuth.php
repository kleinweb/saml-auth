<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\PluginIntegration\Contracts;

// phpcs:disable Squiz.NamingConventions

interface WPSamlAuth extends Plugin
{
    public static function get_instance(): object;

    public function get_provider(): mixed;
}
