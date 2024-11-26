<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\Auth\Entities;

use Illuminate\Support\Facades\File;
use Kleinweb\Auth\Auth;
use OneLogin\Saml2\Constants as Saml;
use Kleinweb\Lib\Support\Environment;
use Illuminate\Support\Facades\Config;

final class IdP extends Entity
{
    public static function config(): array
    {
        return [
            'entityId' => self::entityId(),
            'x509cert' => File::get(self::certPath()),
            'singleSignOnService' => [
                'binding' => Saml::BINDING_HTTP_REDIRECT,
                'url'  => self::loginUrl(),
            ],
            'singleLogoutService' => [
                'binding' => Saml::BINDING_HTTP_REDIRECT,
                'url' => self::logoutUrl(),
            ],
        ];
    }

    public static function entityId(): string
    {
        $default = self::urlBase() . '/shibboleth';

        return Config::string(Auth::CONFIG_PREFIX . 'idp.entityId', $default);
    }

    public static function loginUrl(): string
    {
        return self::urlBase() . '/profile/SAML2/Redirect/SSO';
    }

    public static function logoutUrl(): string
    {
        return self::urlBase() . '/profile/Logout';
    }

    public static function certPath(): string
    {
        return Auth::certPath(self::fqdn());
    }

    public static function fqdn(): string
    {
        return (Environment::isProduction() ? 'fim' : 'np-fim') . '.temple.edu';
    }

    protected static function urlBase(): string
    {
        return 'https://' . self::fqdn() . '/idp';
    }
}
