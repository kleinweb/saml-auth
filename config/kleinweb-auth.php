<?php

// SPDX-FileCopyrightText: (C) 2024 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later


declare(strict_types=1);

use Kleinweb\Lib\Support\Environment;

return [
    // WARNING: Disabling local login will likely result in user lockouts!
    'allow_local_login' => true,

    // Whether to automatically create a user account for arbitrary IdP
    // subjects requesting access.  This should remain disabled.
    'auto_provision' => false,

    'debug' => WP_DEBUG && WP_DEBUG_DISPLAY && (wp_get_environment_type() === Environment::LOCAL),

    // Provisioned users will receive this role.
    'default_role' => \get_option('default_role'),

    'sp' => [
        // Recommended for testing on lower environments.
        // NOTE: The `constant()` function is used to appease PHPStan.
        // 'domain_fallback' => constant('KLEINWEB_PROJECT_DOMAIN'),

        // Manual override for the entity ID.
        // 'entity_id' => 'https://iknowwhatiamdoing.edu'
    ],

    'idp' => [
        // Manual override for the entity ID.
        // 'entity_id' => 'https://iknowwhatiamdoing.edu'
    ],

    'contact' => [
        'support' =>  [
            'givenName' => 'Klein College Digital Initiatives',
            'emailAddress' => 'kleinweb@temple.edu',
        ],
        'technical' =>  [
            'givenName' => 'Klein College Digital Initiatives',
            'emailAddress' => 'kleinweb@temple.edu',
        ],
    ],

    // Leave this as is, unless you know you need to change it.
    'organization' => [
        'name' => 'Klein College of Media and Communication',
        'displayname' => 'Klein College of Media and Communication',
        'url' => 'https://klein.temple.edu',
    ],
];
