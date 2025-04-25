<?php

// SPDX-FileCopyrightText: (C) 2024-2025 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\Auth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Kleinweb\Auth\Support\UserField;
use Kleinweb\Lib\Hooks\Attributes\Action;
use Kleinweb\Lib\Hooks\Attributes\Filter;
use Kleinweb\Lib\Hooks\Traits\Hookable;
use OneLogin\Saml2\Auth as OneLoginAuth;
use Webmozart\Assert\Assert;
use WP_SAML_Auth;

use function remove_action;

final class SamlAuthPluginAdapter
{
    use Hookable;

    public function __construct(protected Settings $librarySettings)
    {
        $this->registerHooks();
        $this->overrideDefaultHooks();
    }

    public static function plugin(): WP_SAML_Auth
    {
        $instance = WP_SAML_Auth::get_instance();
        Assert::isInstanceOf($instance, WP_SAML_Auth::class);

        return $instance;
    }

    public static function saml(): OneLoginAuth
    {
        $provider = self::plugin()->get_provider();
        Assert::isInstanceOf($provider, OneLoginAuth::class);

        return $provider;
    }

    /**
     * Remove the action hooks added by the plugin.
     *
     * Since the hooks were added on `init` at the default priority, they need
     * to be removed *after* that point.
     *
     * @see https://github.com/pantheon-systems/wp-saml-auth/blob/f1e8ba5c2c511296364b8e9d3b5559ec6c057a8d/inc/class-wp-saml-auth.php#L102-L112
     */
    #[Action('init', 11)]
    public function overrideDefaultHooks(): void
    {
        $plugin = WP_SAML_Auth::get_instance();

        remove_action('login_head', [$plugin, 'action_login_head']);
        remove_action('login_message', [$plugin, 'action_login_message']);
        remove_action('login_body_class', [$plugin, 'filter_login_body_class']);
    }

    #[Filter('wp_saml_auth_option')]
    public function handleOption(mixed $value, string $name): mixed
    {
        return $this->settings()->get($name, $value);
    }

    /**
     * @return Collection<string, mixed>
     */
    public function settings(): Collection
    {
        return Collection::make([
            'connection_type' => 'internal',
            'internal_config' => $this->librarySettings->make(),

            'auto_provision' => Config::boolean('kleinweb-auth.auto_provision', false),
            'permit_wp_login' => Config::boolean('kleinweb-auth.allow_local_login', true),
            'default_role' => Config::string(
                'kleinweb-auth.default_role',
                \get_option('default_role'),
            ),

            'get_user_by' => 'login',
            'user_login_attribute' => UserField::LOGIN->samlAttribute(),
            'user_email_attribute' => UserField::EMAIL->samlAttribute(),
            'display_name_attribute' => UserField::DISPLAY_NAME->samlAttribute(),
            'first_name_attribute' => UserField::FIRST_NAME->samlAttribute(),
            'last_name_attribute' => UserField::LAST_NAME->samlAttribute(),
        ]);
    }
}
