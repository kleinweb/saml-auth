<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\Config\SamlEndpoints;

use Spatie\LaravelData\Data;

abstract class Endpoint extends Data
{
    public function __construct(
        public string $name,
        public string $binding,
        public string $url,
        public ?string $responseUrl = null,
    ) {}
}
