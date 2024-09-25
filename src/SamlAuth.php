<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Kleinweb\Lib\Hooks\Traits\Hookable;
use Kleinweb\SamlAuth\Bridge\Contracts\WPSamlAuthPlugin as PluginContract;
use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Error as OneLoginError;

final class SamlAuth
{
    use Hookable;

    final public const NAME = 'kleinweb-saml-auth';

    final public const SHORT_NAME = 'saml-auth';

    public function __construct(
        protected Application $app,
        protected PluginContract $plugin,
        protected OneLoginAuth $provider,
    ) {}

    public function provider(): OneLoginAuth
    {
        return $this->provider;
    }

    public static function isDangerouslyInsecure(): bool
    {
        return Config::boolean(self::SHORT_NAME . 'dangerouslyInsecure', false);
    }

    public static function isDebugEnabled(): bool
    {
        return Config::boolean(SamlAuth::SHORT_NAME . '.debug', Config::boolean('app.debug'));
    }

    /**
     * @throws OneLoginError
     */
    public function metadata(): string
    {
        $settings = $this->provider()->getSettings();
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
