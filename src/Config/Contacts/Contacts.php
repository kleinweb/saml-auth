<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\Config\Contacts;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;

final class Contacts extends Data
{
    #[Computed]
    public Contact $support;

    #[Computed]
    public Contact $technical;

    /**
     * @param string[]|null $support
     * @param string[]|null $technical
     */
    public function __construct(
        ?array $support = null,
        ?array $technical = null,
    ) {
        $this->support = Contact::from($support);
        $this->technical = Contact::from($technical);
    }
}
