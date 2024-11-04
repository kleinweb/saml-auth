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
use Kleinweb\Auth\View\Composers\Login as LoginComposer;
use Kleinweb\Lib\Hooks\Attributes\Action;
use Kleinweb\Lib\Hooks\Attributes\Filter;
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
        $this->app->singleton(Settings::class);

        $this->app->singleton(SamlAuthPluginAdapter::class);

        $this->app->bindIf(OneLoginAuth::class, SamlAuthPluginAdapter::saml(...));

        $this->app->singleton(
            'assets.kleinweb-auth',
            // FIXME: make this less fussy
            fn (): ViteManifest => new ViteManifest(
                $this->package->basePath('../resources/dist/manifest.json'),
                // FIXME: fatal error on subsites!!!  should not depend on
                // absolute path because subsites usually use the first path
                // level as identifier
                Site::url(path: 'vendor/kleinweb/saml-auth/resources/dist/')->toString(),
            ),
        );
    }

    public function boot(): void
    {
        parent::boot();

        $this->app->make(SamlAuthPluginAdapter::class);

        View::composer(LoginComposer::views(), LoginComposer::class);
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
