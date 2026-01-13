<?php

// SPDX-FileCopyrightText: (C) 2025-2026 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\Auth\Support;

use Illuminate\Support\Str;

final class Org
{
    public const DOMAIN = 'temple.edu';

    public static function isUid(string $uid): bool
    {
        return Str::startsWith($uid, 'tu');
    }

    public static function emailAddressify(string $localPart): string
    {
        return $localPart . '@' . self::DOMAIN;
    }
}
