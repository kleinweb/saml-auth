<?php

// SPDX-FileCopyrightText: 2016-2024 Pantheon
// SPDX-FileCopyrightText: 2024 Temple University <kleinweb@temple.edu>
// SPDX-FileContributor: Daniel Bachhuber <daniel@bachhuber.co>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\SamlAuth;

use Args\wp_insert_user as InsertUserArgs;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Kleinweb\Lib\Support\CoreObjects;
use Kleinweb\SamlAuth\Support\UserField;
use League\Uri\Uri;
use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\ValidationError;
use WP_Error;
use WP_User;

use function add_action;
use function add_filter;
use function wp_login_url;

/**
 * Main controller class for WP SAML Auth.
 */
final readonly class SamlAuthPlugin
{
    public function __construct(private OneLoginAuth $provider)
    {
        $this->registerHooks();
    }

    /**
     * Initialize the controller logic on the 'init' hook.
     */
    public function registerHooks(): void
    {
        add_filter('login_body_class', $this->filterLoginBodyClass(...));
        add_action('login_form', self::renderLoginFormAdditions(...));
        add_action('login_footer', self::renderLoginFooterAdditions(...));
        add_action('wp_logout', $this->actionWpLogout(...));

        // Priority after wp_authenticate_username_password runs.
        add_filter('authenticate', $this->filterAuthenticate(...), 21);
    }

    public static function renderLoginFormAdditions(): void
    {
        // phpcs:disable WordPress.Security.EscapeOutput -- False positive.
        echo \view('kleinweb-auth::partials.login-form.cta');
    }

    public static function renderLoginFooterAdditions(): void
    {
        echo \view('kleinweb-auth::partials.login-form.idp-toggle');
    }

    /**
     * Log the user out of the SAML instance when they log out of WordPress.
     *
     * @throws Error
     */
    public function actionWpLogout(): void
    {
        /*
         * Fires before the user is logged out.
         */
        do_action('kleinweb_saml_auth_pre_logout');

        if (! Config::get(SamlAuth::CONFIG_PREFIX . 'idp.singleLogoutService.url')) {
            return;
        }

        $args = [
            'parameters'   => [],
            'nameId'       => null,
            'sessionIndex' => null,
        ];

        /**
         * Permit the arguments passed to the logout() method to be customized.
         *
         * @param array $args existing arguments to be passed
         */
        $args = apply_filters('kleinweb_saml_auth_internal_logout_args', $args);

        $this->provider->logout(
            add_query_arg('loggedout', true, wp_login_url()),
            $args['parameters'],
            $args['nameId'],
            $args['sessionIndex'],
        );

    }

    /**
     * Add body classes for our specific configuration attributes.
     *
     * @param string[] $classes body CSS classes
     *
     * @return string[]
     */
    public static function filterLoginBodyClass($classes): array
    {
        $classes[] = 'is-kleinweb-auth-enabled';
        $classes[] = 'kleinweb-auth--saml';
        $classes[] = SamlAuth::isLocalLoginAllowed()
            ? 'is-local-login-allowed'
            : 'is-local-login-disallowed';

        return $classes;
    }

    /**
     * Check if the user is authenticated against the SAML provider.
     *
     * @param mixed $user wordPress user reference
     *
     * @return false|int|WP_Error|mixed|WP_User|null
     *
     * @throws Error
     */
    public function filterAuthenticate($user): mixed
    {
        $isLocalLoginPermitted = SamlAuth::isLocalLoginAllowed();

        if (($isLocalLoginPermitted && Request::has('SAMLResponse'))
            || ($isLocalLoginPermitted && (Request::query('action') === 'kleinweb-auth'))
            || (!$isLocalLoginPermitted && !Request::has('loggedout'))
            || (!$isLocalLoginPermitted && ($user instanceof WP_User))
        ) {
            $user = $this->doSamlAuthentication();
        }

        return $user;
    }

    /**
     * Do the SAML authentication dance.
     *
     * @throws Error
     */
    public function doSamlAuthentication(): WP_Error|WP_User|null
    {
        if (Request::has('SAMLResponse')) {
            // FIXME: verify this try-catch is what we want -- prevents "unreachable statement" screaming in IDE
            try {
                $this->provider->processResponse();
            } catch (Error $e) {
                Log::notice($e->getMessage());
                // return new WP_Error('kleinweb_saml_auth_response_error', $e->getMessage());
            } catch (ValidationError $e) {
                Log::info($e->getMessage());
                // return new WP_Error('kleinweb_saml_auth_validation_error', $e->getMessage());
            }

            if (!$this->provider->isAuthenticated()) {
                // Translators: Includes error reason from OneLogin.
                return new WP_Error('kleinweb_saml_auth_unauthenticated', sprintf(__('User is not authenticated with SAML IdP. Reason: %s', 'kleinweb-auth'), $this->provider->getLastErrorReason()));
            }

            $attributes = $this->provider->getAttributes();
            $redirectTo = filter_input(INPUT_POST, 'RelayState', FILTER_SANITIZE_URL);
            $permitWpLogin = SamlAuth::isLocalLoginAllowed();
            if ($redirectTo) {
                // When $permit_wp_login=true, we only care about accidentally triggering the redirect
                // to the IdP.  However, when $permit_wp_login=false, hitting wp-login will always
                // trigger the IdP redirect.
                // FIXME: use Uri lib for sane parsing?
                if (($permitWpLogin && (stripos($redirectTo, 'action=kleinweb-auth') === false))
                    || (!$permitWpLogin && (stripos($redirectTo, parse_url(wp_login_url(), PHP_URL_PATH)) === false))) {
                    add_filter('login_redirect', static fn () => $redirectTo, priority: 1);
                }
            }
        } else {
            // FIXME: clean up
            $redirectTo =
                filter_input(INPUT_GET, 'redirect_to', FILTER_SANITIZE_URL)
                    ?: (isset($_SERVER['REQUEST_URI'])
                    ? sanitize_text_field($_SERVER['REQUEST_URI'])
                    : null);

            $this->provider->login($redirectTo);
        }

        $attributes = Collection::make($attributes);
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

        if (! Config::boolean(SamlAuth::CONFIG_PREFIX . 'auto_provision')) {
            return new WP_Error(
                'kleinweb_saml_auth_auto_provision_disabled',
                esc_html__('No WordPress user exists for your account. Please contact your administrator.', 'kleinweb-auth'),
            );
        }

        $userArgs = new InsertUserArgs();

        foreach (UserField::cases() as $field) {
            $userArgs->{$field->dbName()} = $attributes->get($field->samlAttribute());
        }

        $userArgs->role      = Config::string(SamlAuth::CONFIG_PREFIX . 'default_role');
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
}
