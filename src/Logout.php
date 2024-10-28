<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

namespace Kleinweb\Auth;

use Illuminate\Support\Facades\Config;
use Kleinweb\Lib\Hooks\Attributes\Action;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Auth as OneLoginAuth;

final class Logout
{
    public function __construct(private OneLoginAuth $provider) {}

    /**
     * Log the user out of the SAML instance when they log out of WordPress.
     *
     * @throws Error
     */
    #[Action('wp_logout')]
    public function actionWpLogout(): void
    {
        /*
         * Fires before the user is logged out.
         */
        do_action('kleinweb_saml_auth_pre_logout');

        if (!Config::get(Auth::CONFIG_PREFIX . 'idp.singleLogoutService.url')) {
            return;
        }

        $args = [
            'parameters' => [],
            'nameId' => null,
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
}
