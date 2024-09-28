<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\App;
use Kleinweb\Lib\Hooks\Traits\Hookable;
use Kleinweb\SamlAuth\Config\PackageConfig;
use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Error as OneLoginError;

final class SamlAuth
{
    use Hookable;

    final public const NAME = 'kleinweb-saml-auth';

    final public const SHORT_NAME = 'saml-auth';

    final public const CONFIG_PREFIX = self::SHORT_NAME . '.';

    public function __construct(
        protected Application $app,
        protected OneLoginAuth $provider,
    ) {}

    public static function config(): PackageConfig
    {
        return App::make(PackageConfig::class);
    }

    public static function isDangerouslyInsecure(): bool
    {
        return self::config()->dangerouslyInsecure;
    }

    public static function isDebugEnabled(): bool
    {
        return self::config()->debug;
    }

    public static function isLocalLoginAllowed(): bool
    {
        return self::config()->allowlocalLogin;
    }

    /**
     * @throws OneLoginError
     */
    public function metadata(): string
    {
        $settings = $this->provider->getSettings();
        $metadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);

        if (count($errors)) {
            $message = 'Invalid SP metadata: ' . implode(', ', $errors);
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            throw new OneLoginError($message, OneLoginError::METADATA_SP_INVALID);
        }

        return $metadata;
    }
}
