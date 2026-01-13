<?php

// SPDX-FileCopyrightText: (C) 2024-2026 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\Auth;

use Idleberg\WordPress\ViteAssets\Assets;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Kleinweb\Auth\ImportUsers\ImportUsers as ImportUsersFeature;
use Kleinweb\Auth\View\Composers\Login as LoginComposer;
use Kleinweb\Lib\Hooks\Attributes\Action;
use Kleinweb\Lib\Hooks\Attributes\Filter;
use Kleinweb\Lib\Package\Exceptions\InvalidPackage;
use Kleinweb\Lib\Package\Package;
use Kleinweb\Lib\Package\PackageServiceProvider;
use Kleinweb\Lib\Support\Url;
use OneLogin\Saml2\Auth as OneLoginAuth;
use ReflectionException;

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
            ->hasViewComposer(LoginComposer::views(), LoginComposer::class);
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
        $this->app->singleton(ImportUsersFeature::class);
        $this->app->singleton(Settings::class);

        $this->app->singleton(SamlAuthPluginAdapter::class);
        $this->app->bindIf(OneLoginAuth::class, SamlAuthPluginAdapter::saml(...));

        $this->app->singleton(ManagedUser::class);

        $this->app->singleton(
            'assets.kleinweb-auth',
            function (): Assets {
                $manifestFile = $this->package->basePath('../resources/dist/manifest.json');
                $basePath = Url::fromFilesystemPath(dirname($manifestFile))->toString() . '/';

                return new Assets($manifestFile, $basePath, algorithm: ':manifest:');
            },
        );
    }

    public function boot(): void
    {
        parent::boot();

        $this->app->make(SamlAuthPluginAdapter::class);
        $this->app->make(ImportUsersFeature::class)->boot();
        $this->app->make(ManagedUser::class)->boot();

        $this->injectAssets();

        // FIXME: the configurePackage functionality does not handle this properly.
        Blade::componentNamespace('Kleinweb\\Auth\\View\\Components', 'kleinweb-auth');

        // TODO: probably redundant (see configurePackage)
        View::composer(LoginComposer::views(), LoginComposer::class);
    }

    private function injectAssets(): void
    {
        $assets = $this->app->make('assets.kleinweb-auth');
        $assets->inject([
            'resources/css/kleinweb-auth-login.css',
            'resources/js/kleinweb-auth-login.ts',
        ], [
            'action' => 'login_head',
        ]);
    }

    #[Action('login_form')]
    public static function renderLoginFormAdditions(): void
    {
        // phpcs:disable WordPress.Security.EscapeOutput -- False positive.
        echo \view('kleinweb-auth::partials.login-form.cta');
    }

    #[Action('login_footer')]
    public static function renderLoginFooterAdditions(): void
    {
        echo \view('kleinweb-auth::partials.login-form.idp-toggle');
    }

    /**
     * Add body classes for our specific configuration attributes.
     *
     * @param string[] $classes body CSS classes
     *
     * @return string[]
     */
    #[Filter('login_body_class')]
    public static function filterLoginBodyClass($classes): array
    {
        $classes[] = 'is-kleinweb-auth-enabled';
        $classes[] = 'is-saml-authn';
        $classes[] = Auth::isLocalLoginAllowed()
            ? 'is-local-login-allowed'
            : 'is-local-login-disallowed';

        return $classes;
    }
}
