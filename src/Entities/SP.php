<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\Auth\Entities;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use OneLogin\Saml2\Constants as Saml;
use Kleinweb\Auth\Auth;
use Kleinweb\Lib\Support\Environment;
use Kleinweb\Lib\Tenancy\Site;
use Webmozart\Assert\Assert;

final class SP extends Entity
{
    public static function entityId(?int $siteId = null): string
    {
        $path = '/' . (Environment::isProduction() ? 'sp' : 'np-sp');
        $id = 'https://edu.temple.klein.' . self::fqdn($siteId) . $path;

        return Config::string(Auth::CONFIG_PREFIX . 'sp.entityId', $id);
    }

    public static function fqdn(?int $siteId = null): string
    {
        $host = Site::host($siteId);
        Assert::notNull($host);

        if (Environment::isProduction()) {
            return $host;
        }

        $key = Auth::CONFIG_PREFIX . 'sp.domain_fallback';
        $fallback = Config::string($key, $host);
        Assert::stringNotEmpty($fallback);

        return $fallback;
    }

    public static function config(): array
    {
        return [
            'entityId' => self::entityId(),
            'x509cert' => File::get(self::certPath()),
            'privateKey' => File::get(self::keyPath()),
            'assertionConsumerService' => [
                'binding' => Saml::BINDING_HTTP_POST,
                'url'  => self::loginUrl(),
            ],
        ];
    }

    public static function loginUrl(): string
    {
        // FIXME: this might be incorrect logic!?!
        return Site::isPrimaryHost() ? network_home_url('wp-login.php') : wp_login_url();
    }

    public static function logoutUrl(): string
    {
        return '';
    }

    public static function certPath(): string
    {
        return Auth::certPath('sp');
    }

    protected static function keyPath(): string
    {
        return Auth::keyPath('sp');
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
