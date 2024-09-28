<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace Kleinweb\SamlAuth\Config\SamlEndpoints;

use OneLogin\Saml2\Constants as Saml;

final class SingleLogout extends Endpoint
{
    public function __construct(?string $url = '', ?string $responseUrl = null)
    {
        $name = 'singleLogoutService';
        $binding = Saml::BINDING_HTTP_REDIRECT;
        parent::__construct($name, $binding, $url, $responseUrl);
    }
}
