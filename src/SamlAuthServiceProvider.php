<?php

declare(strict_types=1);

namespace Kleinweb\SamlAuth;

use Illuminate\Contracts\Foundation\Application;
use OneLogin\Saml2\Auth as OneLoginAuth;
use Spatie\LaravelPackageTools\Exceptions\InvalidPackage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * SAML Authentication service provider.
 */
final class SamlAuthServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name(SamlAuth::SHORT_NAME)
            ->hasConfigFile()
            ->hasRoute('/../routes/routes');
    }

    /**
     * Register any application services.
     *
     * @throws InvalidPackage
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
}
