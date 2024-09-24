<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth\Providers;

use Illuminate\Support\ServiceProvider;
use Kleinweb\SamlAuth\SamlAuth;
use Kleinweb\SamlAuth\SamlToolkitSettings;
use OneLogin\Saml2\Auth as OneLoginAuth;

/**
 * Kleinweb SAML Auth service provider.
 */
final class SamlAuthServiceProvider extends ServiceProvider
{
    public const PRJ_ROOT = __DIR__ . '/../..';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton(SamlAuth::class);
        $this->app->alias(SamlAuth::class, 'auth.saml');

        $this->app->singleton(SamlToolkitSettings::class);
        $this->app->alias(SamlToolkitSettings::class, 'auth.saml.settings');

        $this->app->alias(OneLoginAuth::class, 'auth.saml.provider');
    }

    public function boot(): void
    {
        $this->publishes([
            self::PRJ_ROOT . '/config/saml-auth.php' => $this->app->configPath('saml-auth.php'),
        ], SamlAuth::SHORT_NAME);

        $this->loadRoutesFrom(self::PRJ_ROOT . '/routes/routes.php');
        $this->loadViewsFrom(self::PRJ_ROOT . '/resources/views', SamlAuth::SHORT_NAME);
        $this->mergeConfigFrom(self::PRJ_ROOT . '/config/saml-auth.php', SamlAuth::SHORT_NAME);
    }
}
