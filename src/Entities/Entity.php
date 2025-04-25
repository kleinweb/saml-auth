<?php

// SPDX-FileCopyrightText: (C) 2024-2025 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\Auth\Entities;

use Kleinweb\Auth\Entities\Contracts\Metadata;

abstract class Entity implements Metadata
{
    /**
     * @return array<string, mixed>
     */
    abstract public static function config(): array;
}
