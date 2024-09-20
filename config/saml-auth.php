<?php

declare(strict_types=1);

return [
    // WARNING: Disabling local login will likely result in user lockouts!
    // 'allow_local_login' => true,

    // Whether to automatically create a user account for arbitrary IdP
    // subjects requesting access.  This should remain disabled.
    // 'auto_provision' => false,

    'debug' => WP_DEBUG && WP_DEBUG_DISPLAY,

    // Provisioned users will receive this role.
    // 'default_role' => \get_option('default_role'),

    'sp' => [
        // Recommended for testing on lower environments.
        // NOTE: The `constant()` function is used to appease PHPStan.
        // 'domainFallback' => constant('KLEINWEB_PROJECT_DOMAIN'),

        // Manual override for the entity ID.
        // 'entityId' => 'https://iknowwhatiamdoing.edu'
    ],

    'idp' => [
        // Manual override for the entity ID.
        // 'entityId' => 'https://iknowwhatiamdoing.edu'
    ],

    'contact' => [
        // "default" may be specified as a placeholder in other contact entries.
        'default' =>  [
            'givenName' => 'Klein College Digital Initiatives',
            'emailAddress' => 'kleinweb@temple.edu',
        ],
        'support' => 'default',
        'technical' => 'default',
    ],

    // Leave this as is, unless you know you need to change it.
    'organization' => [
        'name' => 'Klein College of Media and Communication',
        'displayname' => 'Klein College of Media and Communication',
        'url' => 'https://klein.temple.edu',
    ],
];
