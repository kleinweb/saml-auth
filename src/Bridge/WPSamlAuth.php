<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace Kleinweb\SamlAuth\Bridge;

use Kleinweb\SamlAuth\SamlToolkitSettings;

use function add_filter;

final class WPSamlAuth
{
    public function __construct(protected SamlToolkitSettings $providerSettings)
    {
        $this->registerHooks();
    }

    public function registerHooks(): void
    {
        add_filter('wp_saml_auth_option', $this->filterOption(...), 10, 2);
    }
}
