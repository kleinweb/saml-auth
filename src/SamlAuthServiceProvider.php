<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Kleinweb\Lib\Support\ServiceProvider;
use OneLogin\Saml2\Auth as OneLoginAuth;

/**
 * Kleinweb SAML Auth service provider.
 */
final class SamlAuthServiceProvider extends ServiceProvider
{
    public const PRJ_ROOT = __DIR__ . '/..';
    protected ?Collection $settings;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton(SamlAuth::class);
        $this->app->singleton(SamlToolkitSettings::class);

        $this->app->singleton(SamlAuthPlugin::class);
        $this->app->singleton(
            OneLoginAuth::class,
            static function (Application $app) {
                $providerSettings = $app->make(SamlToolkitSettings::class);

                return new OneLoginAuth($providerSettings->make());
            },
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
