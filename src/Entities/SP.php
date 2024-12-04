<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\Auth\Entities;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use League\Uri\Components\Domain;
use League\Uri\Components\Path;
use League\Uri\Uri;
use OneLogin\Saml2\Constants as Saml;
use Kleinweb\Auth\Auth;
use Kleinweb\Lib\Support\Environment;
use Kleinweb\Lib\Tenancy\Site;

final class SP extends Entity
{
    public static function config(): array
    {
        return [
            'entityId' => self::entityId(),
            'x509cert' => File::get(self::certPath()),
            'privateKey' => File::get(self::keyPath()),
            'assertionConsumerService' => [
                'binding' => Saml::BINDING_HTTP_POST,
                'url'  => self::acsUrl(),
            ],
        ];
    }

    public static function entityId(): string
    {
        $override = Config::string('kleinweb-auth.sp.entity_id');
        if ($override) {
            return $override;
        }

        $uri = Uri::new('https://edu.temple.klein.' . self::domainName());
        $path = Path::new(Environment::isProduction() ? 'sp' : 'np-sp')
            ->withLeadingSlash();

        return $uri->withPath($path)->toString();
    }

    public static function acsUrl(): string
    {
        return Uri::new(self::loginUrl())
            ->withHost(self::domainName())
            ->toString();
    }

    public static function loginUrl(): string
    {
        return wp_login_url();
    }

    public static function logoutUrl(): string
    {
        return '';
    }

    public static function domainName(): string
    {
        if (self::domainOverride()) {
            return Domain::new(self::domainOverride())->toString();
        }

        if (Environment::isProduction()) {
            // The domain of the login URL can be different from the actual
            // site domain.
            $loginUrl = Uri::new(self::loginUrl());

            return Domain::fromUri($loginUrl)->toString();
        }

        return Domain::new(self::domainFallback())->toString();
    }

    public static function domainOverride(): ?string
    {
        return (defined('KLEINWEB_AUTH_SAML_SP_DOMAIN'))
            ? constant('KLEINWEB_AUTH_SAML_SP_DOMAIN')
            : null;
    }

    public static function domainFallback(): string
    {
        return constant('KLEINWEB_PROJECT_DOMAIN');
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
