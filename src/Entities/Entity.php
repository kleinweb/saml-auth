<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\Auth\Entities;

use Kleinweb\Auth\Entities\Contracts\Metadata;

abstract class Entity implements Metadata
{
    protected static string $x509Path = ABSPATH . '/.config/sso/';

    public static string $x509Certificate = '';

    /**
     * @return array<string, mixed>
     */
    abstract public static function config(): array;

    public static function x509Certificate(): string
    {
        if (!static::$x509Certificate) {
            static::$x509Certificate = static::readX509Certificate();
        }

        return static::$x509Certificate;
    }

    abstract protected static function readX509Certificate(): string;
}
