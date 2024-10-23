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
use Illuminate\Support\Str;
use Kleinweb\Lib\Hooks\Attributes\Action;
use Kleinweb\Lib\Package\Exceptions\InvalidPackage;
use Kleinweb\Lib\Package\Package;
use Kleinweb\Lib\Package\PackageServiceProvider;
use Kleinweb\Auth\View\Composers\Login;
use Kleinweb\Lib\Tenancy\Site;
use OneLogin\Saml2\Auth as OneLoginAuth;
use ReflectionException;
use Webmozart\Assert\Assert;

use function file_get_contents;
use function printf;
use function wp_enqueue_script;

use const ABSPATH;

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

        // FIXME: make this less fussy, probably needs custom implementation...
        $distBase = $this->package->basePath('../resources/dist');
        $distBaseRelative = Str::chopStart($distBase, ABSPATH);
        $distBaseUri = Site::url(path: $distBaseRelative . '/')->toString();
        $this->app->singleton(
            'assets.kleinweb-auth',
            static fn (): ViteManifest => new ViteManifest(
                $distBase . '/manifest.json',
                $distBaseUri,
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
    public static function enqueueLoginScripts(): void
    {
        $manifest = App::make('assets.kleinweb-auth');
        Assert::isInstanceOf($manifest, ViteManifest::class);

        wp_enqueue_script(
            'kleinweb-auth-login',
            $manifest->getEntrypoint('resources/js/kleinweb-auth-login.ts')['url'],
        );
    }

    #[Action('login_form')]
    public function printLoginStyles(): void
    {
        $manifest = App::make('assets.kleinweb-auth');
        Assert::isInstanceOf($manifest, ViteManifest::class);

        $handle = 'kleinweb-auth-login';
        $distBase = $this->package->basePath('../resources/dist');
        $styleFile = $manifest->getManifest()["resources/css/{$handle}.css"]['file'];

        printf(
            '<style id="%s-inline-css">%s</style>',
            $handle,
            file_get_contents("{$distBase}/{$styleFile}"),
        );
    }
}
