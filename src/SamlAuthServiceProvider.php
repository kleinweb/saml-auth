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
use Kleinweb\Lib\Hooks\Attributes\Action;
use Kleinweb\Lib\Package\Exceptions\InvalidPackage;
use Kleinweb\Lib\Package\Package;
use Kleinweb\Lib\Package\PackageServiceProvider;
use Kleinweb\Auth\View\Composers\Login;
use OneLogin\Saml2\Auth as OneLoginAuth;
use Webmozart\Assert\Assert;

/**
 * Kleinweb SAML Auth service provider.
 */
final class SamlAuthServiceProvider extends PackageServiceProvider
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
            // FIXME: wrong path? expects resources/dist/
            ->hasAssets()
            // FIXME: reimplement viewcomposer as component
            // ->hasViewComponent('kleinweb-auth', 'TODO')
            ->hasViewComposer(Login::views(), Login::class);
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
        $this->app->singleton(SamlToolkitSettings::class);

        $this->app->singleton(SamlAuthPlugin::class);
        $this->app->singleton(
            OneLoginAuth::class,
            static function (Application $app) {
                $providerSettings = $app->make(SamlToolkitSettings::class);

                return new OneLoginAuth($providerSettings->make());
            },
        );

        $this->app->singleton(
            'assets.kleinweb-auth',
            fn (): ViteManifest => new ViteManifest(
                $this->package->basePath('../public/build/manifest.json'),
                \url($this->package->basePath('public/build/')),
                // \url('public/vendor/'.$this->package->shortName().'/public/')
            ),
        );
    }

    public function boot(): void
    {
        parent::boot();

        // TODO: remove?
        $this->app->make(SamlAuthPlugin::class);

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
