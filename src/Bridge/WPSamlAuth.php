<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace Kleinweb\SamlAuth\Bridge;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Kleinweb\Lib\Hooks\Attributes\Filter;
use Kleinweb\SamlAuth\Bridge\Contracts\Plugin as PluginBridgeContract;
use Kleinweb\SamlAuth\SamlAuth;
use Kleinweb\SamlAuth\SamlToolkitSettings;
use Kleinweb\SamlAuth\Support\UserFields;
use OneLogin\Saml2\Auth as OneLoginAuth;
use WP_SAML_Auth;

use function add_filter;

final class WPSamlAuth implements PluginBridgeContract
{
    public WP_SAML_Auth $plugin;

    public function __construct(protected SamlToolkitSettings $providerSettings)
    {
        $this->registerHooks();

        $this->plugin = WP_SAML_Auth::get_instance();
    }

    public function plugin(): WP_SAML_Auth
    {
        return $this->plugin;
    }

    public function provider(): OneLoginAuth
    {
        return $this->plugin()->get_provider();
    }

    /**
     * @return Collection<string, (bool|string)|array<bool|string>>
     */
    public function settings(): Collection
    {
        return Collection::make(
            [
                'connection_type' => 'internal',
                'allow_local_login' => Config::boolean(SamlAuth::SHORT_NAME . 'allow_local_login', true),
                'auto_provision' => false,
                'default_role' => Config::string('default_role', get_option('default_role')),
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

    public function registerHooks(): void
    {
        add_filter('wp_saml_auth_option', $this->filterOption(...), 10, 2);
    }

    /**
     *  Configure WP-SAML-Auth settings.
     *
     *  Unfortunately, this is the only filter provided by the
     *  WP-SAML-Auth plugin for the purpose of declaring settings.
     *
     * @param string $key
     */
    public function filterOption(mixed $value, $key): mixed
    {
        return $this->settings()->get($key, $value);
    }
}
