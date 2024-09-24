<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Kleinweb\SamlAuth\Support\UserFields;
use Kleinweb\Lib\Hooks\Attributes\Filter;
use Kleinweb\Lib\Hooks\Traits\Hookable;
use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Error as OneLoginError;

use function get_option;

final class SamlAuth
{
    use Hookable;

    final public const NAME = 'kleinweb-saml-auth';

    final public const SHORT_NAME = 'saml-auth';

    public function __construct(
        protected Application $app,
        protected OneLoginAuth $provider,
        protected SamlToolkitSettings $providerSettings,
    ) {
        $this->registerHooks();
    }

    public static function config(string $path, mixed $default = null): mixed
    {
        return Config::get(self::SHORT_NAME . '.' . $path, $default);
    }

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
     * @return Collection<string, (bool|string)|array<bool|string>>
     */
    public function settings(): Collection
    {
        return Collection::make(
            [
                'connection_type' => 'internal',
                'allow_local_login' => Config::boolean(self::SHORT_NAME . 'allow_local_login', true),
                'auto_provision' => false,
                'default_role' => self::config('default_role', get_option('default_role')),
                'get_user_by' => 'login',
                'user_login_attribute' => UserFields::LOGIN,
                'user_email_attribute' => UserFields::EMAIL,
                'display_name_attribute' => UserFields::DISPLAY_NAME,
                'first_name_attribute' => UserFields::FIRST_NAME,
                'last_name_attribute' => UserFields::LAST_NAME,
                'internal_config' => $this->providerSettings->make(),
            ],
        );
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

    /**
     *  Configure WP-SAML-Auth settings.
     *
     *  Unfortunately, this is the only filter provided by the
     *  WP-SAML-Auth plugin for the purpose of declaring settings.
     *
     * @param string $key
     */
    #[Filter('wp_saml_auth_option')]
    public function filterBasePluginOption(mixed $value, $key): mixed
    {
        return $this->settings()->get($key, $value);
    }
}
