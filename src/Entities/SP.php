<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\Entities;

use Illuminate\Support\Facades\Config;
use OneLogin\Saml2\Constants as Saml;
use Kleinweb\SamlAuth\SamlAuth;
use Kleinweb\Lib\Support\Environment;
use Kleinweb\Lib\Tenancy\Site;
use Webmozart\Assert\Assert;

final class SP extends SamlEntity
{
    public static function entityId(?int $siteId = null): string
    {
        $path = '/' . (Environment::isProduction() ? 'sp' : 'np-sp');
        $id = 'https://edu.temple.klein.' . self::fqdn($siteId) . $path;

        return Config::string(SamlAuth::SHORT_NAME . '.sp.entityId', $id);
    }

    public static function fqdn(?int $siteId = null): string
    {
        $fqdn = Site::host($siteId);

        // Defaults back to the FQDN to ensure a valid return value.
        $fallback = Config::string(SamlAuth::SHORT_NAME . '.sp.domainFallback', $fqdn);
        Assert::stringNotEmpty($fallback);

        return Environment::isProduction() ? $fqdn : $fallback;
    }

    public static function config(): array
    {
        /* @phpstan-ignore-next-line */
        return [
            'entityId' => self::entityId(),
            'x509cert' => self::x509Certificate(),
            'privateKey' => self::readX509PrivateKey(),
            'assertionConsumerService' => [
                'binding' => Saml::BINDING_HTTP_POST,
                'url'  => self::acsUrl(),
            ],
        ];
    }

    public static function acsUrl(): string
    {
        return self::loginUrl();
    }

    public static function loginUrl(): string
    {
        return Site::isPrimaryFqdn() ? network_home_url('wp-login.php') : wp_login_url();
    }

    public static function logoutUrl(): string
    {
        return '';
    }

    protected static function readX509Certificate(): string
    {
        return file_get_contents(ABSPATH . '/.config/sso/sp.crt');
    }

    public static function readX509PrivateKey(): string
    {
        return file_get_contents(ABSPATH . '/.config/sso/sp.key.pem');
    }

    public static function name(): string
    {
        $prefix = Environment::isProduction() ? '' : '[TEST] ';

        return $prefix . Site::name();
    }

    public static function description(): string
    {
        return Site::description();
    }
}
