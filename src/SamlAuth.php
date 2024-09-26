<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Kleinweb\Lib\Hooks\Traits\Hookable;
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

    public static function isDangerouslyInsecure(): bool
    {
        return Config::boolean(
            self::CONFIG_PREFIX . 'dangerouslyInsecure',
            false,
        );
    }

    public static function isDebugEnabled(): bool
    {
        return Config::boolean(
            SamlAuth::CONFIG_PREFIX . 'debug',
            Config::boolean('app.debug'),
        );
    }

    public static function isLocalLoginPermitted(): bool
    {
        return Config::boolean(
            self::CONFIG_PREFIX . 'permit_wp_login',
            true,
        );
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
