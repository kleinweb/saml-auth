<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\Config;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

final class Organization extends Data
{
    public function __construct(
        public string $name,
        // FIXME: is this right?
        #[MapInputName('displayname')]
        public string $displayName,
        public string $url,
    ) {}
}
