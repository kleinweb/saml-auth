<?php

// SPDX-FileCopyrightText: (C) 2024-2026 Temple University <kleinweb@temple.edu>
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

use function is_multisite;
use function network_site_url;
use function wp_login_url;

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
        if (Config::has('kleinweb-auth.sp.entity_id')) {
            return Config::string('kleinweb-auth.sp.entity_id');
        }

        $uri = Uri::new('https://edu.temple.klein.' . self::entityDomain());
        $isProd = (Environment::isProduction() || Environment::isMigration());
        $path = Path::new($isProd ? 'sp' : 'np-sp')
            ->withLeadingSlash();

        return $uri->withPath($path)->toString();
    }

    /**
     * Fully-qualified domain name for generating the entity ID.
     */
    public static function entityDomain(): string
    {
        $domain = match (constant('WP_ENV')) {
            Environment::PRODUCTION => self::serviceDomain(),
            Environment::STAGING => self::domainFallback(),
            default => self::migratedDomain() ?? self::domainFallback(),
        };

        return Domain::new($domain)->toString();
    }

    public static function migratedDomain(): ?string
    {
        if (!is_multisite()) {
            return null;
        }

        $id = get_current_blog_id();
        $domain = get_site_meta($id, 'orig_host', single: true);

        return $domain ? Domain::new($domain)->toString() : null;
    }

    public static function serviceDomain(): string
    {
        $loginUrl = Uri::new(self::loginUrl());

        return Domain::fromUri($loginUrl)->toString();
    }

    public static function domainFallback(): string
    {
        return constant('KLEINWEB_PROJECT_DOMAIN');
    }

    /**
     * Get the URL to the SAML `AssertionConsumerService` endpoint.
     */
    public static function acsUrl(): string
    {
        $loginUri = Uri::new(self::loginUrl());

        if (Environment::isMigration()) {
            $host = self::migratedDomain() ?? self::domainFallback();
            $loginUri = $loginUri->withHost($host);
        }

        return $loginUri->toString();
    }

    /**
     * Get the WordPress login URL appropriate for the current SAML SP.
     *
     * We need to reduce the proliferation of per-site login URLs as much as
     * possible to avoid generating different `AssertionConsumerService`
     * endpoints for each non-domain-mapped subsite in a subdirectory-based
     * multisite instance.  The actual ACS endpoint must match the ACS endpoint
     * in the SP metadata provided to the IdP.
     */
    public static function loginUrl(): string
    {
        if (!is_multisite()) {
            return wp_login_url();
        }

        return Site::isPrimaryHost()
            ? network_site_url('wp-login.php')
            : wp_login_url();
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
