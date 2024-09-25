<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace Kleinweb\SamlAuth\Bridge;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Kleinweb\SamlAuth\Bridge\Contracts\WPSamlAuthPlugin as WPSamlAuthPluginContract;
use Kleinweb\SamlAuth\SamlAuth;
use Kleinweb\SamlAuth\SamlToolkitSettings;
use Kleinweb\SamlAuth\Support\UserFields;

use function add_filter;

final class WPSamlAuth
{
    protected object $plugin;
    protected object $provider;
    /**
     * @var Collection<string, (bool|string)|array<bool|string>>|null
     */
    protected ?Collection $settings;

    public function __construct(
        WPSamlAuthPluginContract $plugin,
        protected SamlToolkitSettings $providerSettings,
    ) {
        $this->registerHooks();

        $this->plugin = $plugin->get_instance();
        $this->provider = $plugin->get_provider();
    }

    public function plugin(): object
    {
        return $this->plugin;
    }

    public function provider(): object
    {
        return $this->provider;
    }

    /**
     * @return Collection<string, (bool|string)|array<bool|string>>
     */
    public function settings(): Collection
    {
        if (!$this->settings) {
            $this->settings = Collection::make(
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

        return $this->settings;
    }

    protected function registerHooks(): void
    {
        add_filter('wp_saml_auth_option', $this->filterPluginOption(...), 10, 2);
    }

    /**
     *  Configure WP-SAML-Auth settings.
     *
     *  Unfortunately, this is the only filter provided by the
     *  WP-SAML-Auth plugin for the purpose of declaring settings.
     *
     * @param string $key
     */
    public function filterPluginOption(mixed $value, $key): mixed
    {
        return $this->settings()->get($key, $value);
    }
}
