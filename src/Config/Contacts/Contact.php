<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\Config\Contacts;

use Spatie\LaravelData\Data;

final class Contact extends Data
{
    public function __construct(
        public string $givenName = 'Klein College Digital Initiatives',
        public string $emailAddress = 'kleinweb@temple.edu',
    ) {}
}
