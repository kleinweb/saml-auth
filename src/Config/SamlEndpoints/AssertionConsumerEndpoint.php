<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\Config\SamlEndpoints;

final class AssertionConsumerEndpoint extends Endpoint
{
    public function __construct(string $url, ?string $responseUrl = null)
    {
        $name = 'assertionConsumerService';
        $binding = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';
        parent::__construct($name, $binding, $url, $responseUrl);
    }
}
