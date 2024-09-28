<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\Config;

use Kleinweb\SamlAuth\Config\Contacts\Contacts;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

use const WP_DEBUG;
use const WP_DEBUG_DISPLAY;

#[MapInputName(SnakeCaseMapper::class)]
final class PackageConfig extends Data
{
    public function __construct(
        public SamlServiceProvider $sp,
        public SamlIdentityProvider $idp,
        #[MapInputName('contact')]
        public Contacts $contacts,
        public Organization $organization,
        public bool $allowlocalLogin,
        public bool $autoProvision,
        public string $defaultRole,
        public bool $debug = WP_DEBUG && WP_DEBUG_DISPLAY,
        #[MapInputName('dangerously_insecure')]
        public bool $dangerouslyInsecure = false,
    ) {}
}
