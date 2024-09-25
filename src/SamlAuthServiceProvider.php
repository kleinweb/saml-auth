<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Kleinweb\Lib\Hooks\Attributes\Filter;
use Kleinweb\Lib\Support\ServiceProvider;
use Kleinweb\SamlAuth\Support\UserFields;

/**
 * Kleinweb SAML Auth service provider.
 */
final class SamlAuthServiceProvider extends ServiceProvider
{
    public const PRJ_ROOT = __DIR__ . '/..';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton(SamlAuth::class);
        $this->app->singleton(SamlToolkitSettings::class);

        $this->app->singleton('saml-auth.plugin');
        $this->app->singleton('saml-auth.provider', static fn (\Illuminate\Foundation\Application $app) => $app->make('saml-auth.plugin')->get_provider());
    }

    public function boot(): void
    {
        parent::boot();

        $this->publishes([
            self::PRJ_ROOT . '/config/saml-auth.php' => $this->app->configPath('saml-auth.php'),
        ], SamlAuth::SHORT_NAME);

        $this->loadRoutesFrom(self::PRJ_ROOT . '/routes/routes.php');
        $this->loadViewsFrom(self::PRJ_ROOT . '/resources/views', SamlAuth::SHORT_NAME);
        $this->mergeConfigFrom(self::PRJ_ROOT . '/config/saml-auth.php', SamlAuth::SHORT_NAME);
    }

    /**
     * @return Collection<string, (bool|string)|array<bool|string>>
     *
     * @throws BindingResolutionException
     */
    public function settings(): Collection
    {
        // FIXME: creates new collection on every call!  use a property.
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
                'internal_config' => $this->app->make(SamlToolkitSettings::class)->make(),
            ],
        );
    }

    /**
     *  Configure WP-SAML-Auth settings.
     *
     *  Unfortunately, this is the only filter provided by the
     *  WP-SAML-Auth plugin for the purpose of declaring settings.
     *
     * @param string $key
     *
     * @throws BindingResolutionException
     */
    #[Filter('wp_saml_auth_option')]
    public function filterPluginOption(mixed $value, $key): mixed
    {
        return $this->settings()->get($key, $value);
    }
}
