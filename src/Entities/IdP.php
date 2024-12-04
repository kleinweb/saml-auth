<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\Auth\Entities;

use Exception;
use Illuminate\Support\Facades\File;
use Kleinweb\Auth\Auth;
use Kleinweb\Auth\Support\IdpEnvironment;
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

        return Config::string('kleinweb-auth.idp.entity_id', $default);
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
        return Auth::certPath(self::domainName());
    }

    public static function domainName(): string
    {
        $subdomain = match (self::targetEnv()) {
            IdpEnvironment::PRODUCTION => 'fim',
            IdpEnvironment::NONPROD => 'np-fim',
        };

        return $subdomain . '.temple.edu';
    }

    protected static function urlBase(): string
    {
        return 'https://' . self::domainName() . '/idp';
    }

    public static function targetEnvOverride(): ?string
    {
        return defined('KLEINWEB_AUTH_SAML_IDP_ENV')
            ? constant('KLEINWEB_AUTH_SAML_IDP_ENV')
            : null;
    }

    /**
     * @throws Exception
     */
    public static function targetEnv(): IdpEnvironment
    {
        $override = self::targetEnvOverride();

        if ($override !== null) {
            return match ($override) {
                'fim', 'prod', 'production' => IdpEnvironment::PRODUCTION,
                'np-fim', 'nonprod', 'nonproduction', 'np' => IdpEnvironment::NONPROD,
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped, SlevomatCodingStandard.Files.LineLength.LineTooLong
                default => throw new Exception("KLEINWEB_AUTH_SAML_IDP_ENV: Invalid target IdP identifier '{$override}'"),
            };
        }

        return Environment::isProduction()
            ? IdpEnvironment::PRODUCTION
            : IdpEnvironment::NONPROD;
    }
}
