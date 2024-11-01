<?php

// SPDX-FileCopyrightText: 2016-2024 Pantheon
// SPDX-FileCopyrightText: 2024 Temple University <kleinweb@temple.edu>
// SPDX-FileContributor: Daniel Bachhuber <daniel@bachhuber.co>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\Auth;

use Args\wp_insert_user as InsertUserArgs;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Kleinweb\Lib\Hooks\Attributes\Action;
use Kleinweb\Lib\Hooks\Attributes\Filter;
use Kleinweb\Lib\Hooks\Traits\Hookable;
use Kleinweb\Lib\Support\CoreObjects;
use Kleinweb\Auth\Support\UserField;
use League\Uri\Components\Path;
use League\Uri\Components\Query;
use League\Uri\Uri;
use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\ValidationError;
use WP_Error;
use WP_User;

use function add_filter;
use function wp_login_url;

/**
 * Main controller class for WP SAML Auth.
 */
final readonly class Login
{
    use Hookable;

    public function __construct(private OneLoginAuth $provider)
    {
        $this->registerHooks();
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

    /**
     * Check if the user is authenticated against the SAML provider.
     *
     * Priority value `21` ensures this filter runs after the
     * `wp_authenticate_username_password` filter.
     *
     * @param mixed $user wordPress user reference
     *
     * @return false|int|WP_Error|mixed|WP_User|null
     *
     * @throws Error
     */
    #[Filter('authenticate', priority: 21)]
    public function filterAuthenticate($user): mixed
    {
        $isLocalLoginPermitted = Auth::isLocalLoginAllowed();

        if (($isLocalLoginPermitted && Request::has('SAMLResponse'))
            || ($isLocalLoginPermitted && (Request::query('action') === 'kleinweb-auth'))
            || (!$isLocalLoginPermitted && !Request::has('loggedout'))
            || (!$isLocalLoginPermitted && ($user instanceof WP_User))
        ) {
            $user = $this->doSamlAuthentication();
        }

        return $user;
    }

    protected function processResponse(): mixed
    {
        try {
            return $this->provider->processResponse();
        } catch (Error $e) {
            return new WP_Error('kleinweb_saml_auth_response_error', $e->getMessage());
        } catch (ValidationError $_e) {
            $reason = $this->provider->getLastErrorReason();

            return new WP_Error('kleinweb_saml_auth_validation_error', sprintf('Authentication failed: %s', $reason));
        }
    }

    protected function findOrCreateUser(): WP_Error|WP_User|int|null
    {
        $attributes = Collection::make($this->provider->getAttributes());
        if ($attributes->isEmpty()) {
            return new WP_Error(
                'kleinweb_saml_auth_no_attributes',
                esc_html__('No attributes were present in SAML response. Attributes are used to create and fetch users. Please contact your administrator', 'kleinweb-auth'),
            );
        }

        $uidAttributeName = UserField::LOGIN->samlAttribute();
        $uidAttribute = (array) $attributes->get($uidAttributeName, []);
        $uid = Arr::first($uidAttribute);
        if (!$uid) {
            // Translators: Communicates how the user is fetched based on the SAML response.
            return new WP_Error(
                'kleinweb_saml_auth_missing_attribute',
                sprintf(esc_html__('"%1$s" attribute is expected, but missing, in SAML response. Attribute is used to fetch existing user by AccessNet username. Please contact your administrator.', 'kleinweb-auth'), $uidAttributeName),
            );
        }

        $existingUser = CoreObjects::getUserBy('login', $uid);
        if ($existingUser) {
            /*
             * Runs after a existing user has been authenticated in WordPress
             *
             * @param WP_User $existing_user  The existing user object.
             * @param array   $attributes     All attributes received from the SAML Response
             */
            do_action('kleinweb_saml_auth_existing_user_authenticated', $existingUser, $attributes);

            return $existingUser;
        }

        if (! Config::boolean(Auth::CONFIG_PREFIX . 'auto_provision')) {
            return new WP_Error(
                'kleinweb_saml_auth_auto_provision_disabled',
                esc_html__('No WordPress user exists for your account. Please contact your administrator.', 'kleinweb-auth'),
            );
        }

        $userArgs = new InsertUserArgs();

        foreach (UserField::cases() as $field) {
            $userArgs->{$field->dbName()} = $attributes->get($field->samlAttribute());
        }

        $userArgs->role      = Config::string(Auth::CONFIG_PREFIX . 'default_role');
        $userArgs->user_pass = wp_generate_password();

        /**
         * Runs before a user is created based off a SAML response.
         *
         * @param array $userArgs   arguments passed to wp_insert_user()
         * @param array $attributes attributes from the SAML response
         */
        $userArgs = apply_filters(
            'kleinweb_saml_auth_insert_user',
            $userArgs->toArray(),
            $attributes->toArray(),
        );

        $userId   = wp_insert_user($userArgs);
        if (is_wp_error($userId)) {
            return $userId;
        }

        $user = CoreObjects::getUserBy('id', $userId);

        /*
         * Runs after the user has been authenticated in WordPress
         *
         * @param WP_User $user       The new user object.
         * @param array   $attributes All attributes received from the SAML Response
         */
        do_action('kleinweb_saml_auth_new_user_authenticated', $user, $attributes);

        return $user;
    }

    /**
     * Do the SAML authentication dance.
     *
     * @throws Error
     */
    public function doSamlAuthentication(): mixed
    {
        $redirectTo = self::redirectUri()?->toString();

        if (Request::has('SAMLResponse')) {
            $response = $this->processResponse();

            if ($response instanceof WP_Error) {
                return $response;
            }
        } else {
            $this->provider->login($redirectTo);
        }

        if (!$this->provider->isAuthenticated()) {
            return                new WP_Error(
                'kleinweb_saml_auth_unauthenticated',
                // Translators: Includes error reason from OneLogin.
                sprintf(
                    __('User is not authenticated with SAML IdP. Reason: %s', 'kleinweb-auth'),
                    $this->provider->getLastErrorReason(),
                ),
            );
        }

        if ($this->shouldRedirect()) {
            add_filter('login_redirect', static fn () => $redirectTo, priority: 1);
        }

        return $this->findOrCreateUser();
    }

    public static function redirectUri(): ?Uri
    {
        $url = Request::has('SAMLResponse')
            ? Request::post('RelayState')
            : Request::get('redirect_to', Request::getRequestUri());

        return $url ? Uri::new($url) : null;
    }

    /**
     * Whether the client should redirect the user after authentication.
     *
     * When $permit_wp_login=true, we only care about accidentally triggering the redirect
     * to the IdP.  However, when $permit_wp_login=false, hitting wp-login will always
     * trigger the IdP redirect.
     */
    protected function shouldRedirect(): bool
    {
        $redirectTo = self::redirectUri();

        if (!$redirectTo) {
            return false;
        }

        $loginUrlPath = Path::new(wp_login_url());
        $query = Query::fromUri($redirectTo);

        return Auth::isLocalLoginAllowed()
            ? ! $query->hasPair('action', 'kleinweb-auth')
            : ! Str::contains($redirectTo->getPath(), $loginUrlPath->toString());
    }
}
