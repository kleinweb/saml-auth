<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

// phpcs:disable Squiz.NamingConventions.ValidFunctionName.ScopeNotCamelCaps

namespace Kleinweb\SamlAuth\Bridge\Contracts;

use OneLogin\Saml2\Auth;

interface WPSamlAuthPlugin
{
    public function get_instance(): object;

    public function get_provider(): Auth;
}
