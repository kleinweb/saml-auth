<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\Entities;

use Kleinweb\SamlAuth\SamlAuth;
use OneLogin\Saml2\Constants as Saml;
use Kleinweb\Lib\Support\Environment;
use Illuminate\Support\Facades\Config;

use function file_get_contents;

final class IdP extends SamlEntity
{
    public static function config(): array
    {
        /* @phpstan-ignore-next-line */
        return [
            'entityId' => self::entityId(),
            'x509cert' => self::x509Certificate(),
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

        return Config::string(SamlAuth::CONFIG_PREFIX . 'idp.entityId', $default);
    }

    public static function loginUrl(): string
    {
        return self::urlBase() . '/profile/SAML2/Redirect/SSO';
    }

    public static function logoutUrl(): string
    {
        return self::urlBase() . '/profile/Logout';
    }

    protected static function readX509Certificate(): string
    {
        $certPath = self::$x509Path . self::fqdn() . '.crt';
        self::$x509Certificate = file_get_contents($certPath) ?: '';

        return self::$x509Certificate;
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
