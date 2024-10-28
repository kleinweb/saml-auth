<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\Auth\Entities\Contracts;

interface Metadata
{
    public static function entityId(): string;

    public static function fqdn(): string;

    public static function loginUrl(): string;

    public static function logoutUrl(): string;

    public static function x509Certificate(): string;
}
