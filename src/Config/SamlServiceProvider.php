<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\Config;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
final class SamlServiceProvider extends Data
{
    public Collection $args;

    public function __construct(
        public ?string $entityId = null,
        public ?string $domainFallback = null,
        array ...$args,
    ) {
        if (!$args) {
            return;
        }

        $this->args = Collection::make($args);
    }
}
