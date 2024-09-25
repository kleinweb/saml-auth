<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth;

use Illuminate\Contracts\Foundation\Application;
use Kleinweb\Lib\Support\ServiceProvider;
use Kleinweb\SamlAuth\Bridge\Contracts\WPSamlAuthPlugin as PluginContract;
use Kleinweb\SamlAuth\Bridge\WPSamlAuth;
use OneLogin\Saml2\Auth;

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

        $this->app->singleton(PluginContract::class);
        $this->app->singleton(WPSamlAuth::class);

        $this->app->bind(
            Auth::class,
            static fn (Application $app) => $app->make(PluginContract::class)->get_provider(),
        );
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
}
