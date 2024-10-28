<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\Auth;

use Idleberg\ViteManifest\Manifest as ViteManifest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Kleinweb\Auth\View\Composers\Login;
use Kleinweb\Lib\Hooks\Attributes\Action;
use Kleinweb\Lib\Package\Exceptions\InvalidPackage;
use Kleinweb\Lib\Package\Package;
use Kleinweb\Lib\Package\PackageServiceProvider;
use Kleinweb\Lib\Tenancy\Site;
use OneLogin\Saml2\Auth as OneLoginAuth;
use ReflectionException;
use Webmozart\Assert\Assert;

/**
 * Kleinweb SAML Auth service provider.
 */
final class AuthServiceProvider extends PackageServiceProvider
{
    /**
     * @var Collection<string, mixed>|null
     */
    protected ?Collection $settings;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('kleinweb-auth')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('routes')
            ->hasAssets()
            // FIXME: reimplement viewcomposer as component
            // ->hasViewComponent('kleinweb-auth', 'TODO')
            ->hasViewComposer(Login::views(), Login::class);
    }

    /**
     * Register any application services.
     *
     * @throws InvalidPackage
     * @throws ReflectionException
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton(Auth::class);
        $this->app->singleton(Settings::class);

        $this->app->singleton(Login::class);
        $this->app->singleton(Logout::class);
        $this->app->singleton(
            OneLoginAuth::class,
            static function (Application $app) {
                $providerSettings = $app->make(Settings::class);

                return new OneLoginAuth($providerSettings->make());
            },
        );

        $this->app->singleton(
            'assets.kleinweb-auth',
            fn (): ViteManifest => new ViteManifest(
                $this->package->basePath('../resources/dist/manifest.json'),
                // FIXME: make this less fussy
                Site::url(path: '/vendor/kleinweb/saml-auth/resources/dist/')->toString(),
            ),
        );
    }

    public function boot(): void
    {
        parent::boot();

        // TODO: remove?
        $this->app->make(Login::class);

        View::composer(Login::views(), Login::class);
    }

    #[Action('login_enqueue_scripts')]
    public static function enqueueLoginStyles(): void
    {
        $manifest = App::make('assets.kleinweb-auth');
        Assert::isInstanceOf($manifest, ViteManifest::class);

        wp_enqueue_style(
            'kleinweb-auth',
            $manifest->getEntrypoint('resources/css/kleinweb-auth-login.css')['url'],
        );
        wp_enqueue_script(
            'kleinweb-auth',
            $manifest->getEntrypoint('resources/js/kleinweb-auth-login.ts')['url'],
        );
    }
}
