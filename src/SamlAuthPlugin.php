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

/**
 * Main controller class for WP SAML Auth.
 */
final readonly class SamlAuthPlugin
{
    public function __construct(private OneLoginAuth $provider)
    {
        $this->registerHooks();
    }

    public function registerHooks(): void
    {
        // TODO: why?
        add_action('init', $this->registerHooksOnInit(...));
    }

    /**
     * Initialize the controller logic on the 'init' hook.
     */
    public function registerHooksOnInit(): void
    {
        add_action('login_head', self::actionLoginHead(...));
        add_action('wp_logout', $this->actionWpLogout(...));

        add_filter('login_message', self::filterLoginMessage(...));
        add_filter('login_body_class', $this->filterLoginBodyClass(...));
        // Priority after wp_authenticate_username_password runs.
        add_filter('authenticate', $this->filterAuthenticate(...), 21);
    }

    /**
     * Render CSS on the login screen.
     */
    public static function actionLoginHead(): void
    {
        if (! did_action('login_form_login')) {
            return;
        }

        ?>
        <style>
            #wp-saml-auth-cta {
                background: #fff;
                -webkit-box-shadow: 0 1px 3px rgba(0,0,0,.13);
                box-shadow: 0 1px 3px rgba(0,0,0,.13);
                padding: 26px 24px 26px;
                margin-top: 24px;
                margin-bottom: 24px;
            }
            .wp-saml-auth-deny-wp-login #loginform,
            .wp-saml-auth-deny-wp-login #nav {
                display: none;
            }
        </style>
        <?php
    }

    /**
     * HACK: Add the button to sign in with SAML provider.
     *
     * @param string $message existing message string
     */
    public static function filterLoginMessage($message): string
    {

        if (! SamlAuth::isLocalLoginPermitted() || ! did_action('login_form_login')) {
            return $message;
        }

        $strings = [
            'title'     => __('Use one-click authentication:', 'wp-saml-auth'),
            'button'    => __('Sign In', 'wp-saml-auth'),
            'alt_title' => __('Or, sign in with WordPress:', 'wp-saml-auth'),
        ];

        $queryArgs  = [
            'action' => 'wp-saml-auth',
        ];

        $redirectTo = filter_input(INPUT_GET, 'redirect_to', FILTER_SANITIZE_URL);
        if ($redirectTo) {
            $queryArgs['redirect_to'] = rawurlencode($redirectTo);
        }

        /**
         * Permit login screen text strings to be easily customized.
         *
         * @param array $strings existing text strings
         */
        $strings = apply_filters('wp_saml_auth_login_strings', $strings);
        echo '<h3><em>' . esc_html($strings['title']) . '</em></h3>';
        echo '<div id="wp-saml-auth-cta"><p><a class="button" href="' . esc_url(add_query_arg($queryArgs, wp_login_url())) . '">' . esc_html($strings['button']) . '</a></p></div>';
        echo '<h3><em>' . esc_html($strings['alt_title']) . '</em></h3>';

        return $message;
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
        do_action('wp_saml_auth_pre_logout');

        if (!Config::get(SamlAuth::SHORT_NAME . 'idp.singleLogoutService.url')) {
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
        $args = apply_filters('wp_saml_auth_internal_logout_args', $args);

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
        if (! SamlAuth::isLocalLoginPermitted()) {
            $classes[] = 'wp-saml-auth-deny-wp-login';
        }

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
        $isLocalLoginPermitted = SamlAuth::isLocalLoginPermitted();

        if (($isLocalLoginPermitted && Request::has('SAMLResponse'))
            || ($isLocalLoginPermitted && (Request::query('action') === 'wp-saml-auth'))
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
    public function doSamlAuthentication()
    {
        if (Request::has('SAMLResponse')) {
            // FIXME: verify this try-catch is what we want -- prevents "unreachable statement" screaming in IDE
            try {
                $this->provider->processResponse();
            } catch (Error $e) {
                Log::notice($e->getMessage());
                // return new WP_Error('wp_saml_auth_response_error', $e->getMessage());
            } catch (ValidationError $e) {
                Log::info($e->getMessage());
                // return new WP_Error('wp_saml_auth_validation_error', $e->getMessage());
            }

            if (!$this->provider->isAuthenticated()) {
                // Translators: Includes error reason from OneLogin.
                return new WP_Error('wp_saml_auth_unauthenticated', sprintf(__('User is not authenticated with SAML IdP. Reason: %s', 'wp-saml-auth'), $this->provider->getLastErrorReason()));
            }

            $attributes = $this->provider->getAttributes();
            $redirectTo = filter_input(INPUT_POST, 'RelayState', FILTER_SANITIZE_URL);
            $permitWpLogin = SamlAuth::isLocalLoginPermitted();
            if ($redirectTo) {
                // When $permit_wp_login=true, we only care about accidentally triggering the redirect
                // to the IdP.  However, when $permit_wp_login=false, hitting wp-login will always
                // trigger the IdP redirect.
                // FIXME: use Uri lib for sane parsing?
                if (($permitWpLogin && (stripos($redirectTo, 'action=wp-saml-auth') === false))
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
                'wp_saml_auth_no_attributes',
                esc_html__('No attributes were present in SAML response. Attributes are used to create and fetch users. Please contact your administrator', 'wp-saml-auth'),
            );
        }

        $uidAttributeName = UserField::LOGIN->samlAttribute();
        $uidAttribute = $attributes->get($uidAttributeName, []);
        $uid = Arr::first($uidAttribute);
        if (!$uid) {
            // Translators: Communicates how the user is fetched based on the SAML response.
            return new WP_Error(
                'wp_saml_auth_missing_attribute',
                sprintf(esc_html__('"%1$s" attribute is expected, but missing, in SAML response. Attribute is used to fetch existing user by AccessNet username. Please contact your administrator.', 'wp-saml-auth'), $uidAttributeName),
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
            do_action('wp_saml_auth_existing_user_authenticated', $existingUser, $attributes);

            return $existingUser;
        }

        if (! Config::boolean(SamlAuth::CONFIG_PREFIX . 'auto_provision')) {
            return new WP_Error(
                'wp_saml_auth_auto_provision_disabled',
                esc_html__('No WordPress user exists for your account. Please contact your administrator.', 'wp-saml-auth'),
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
            'wp_saml_auth_insert_user',
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
        do_action('wp_saml_auth_new_user_authenticated', $user, $attributes);

        return $user;
    }
}
