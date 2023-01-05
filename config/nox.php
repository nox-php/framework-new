<?php

return [
    'settings' => [
        'default' => 'file',

        'drivers' => [
            'file' => [
                'disk' => 'local',

                'path' => 'settings.json',
            ],
        ],
    ],

    'auth' => [
        'routes' => [
            'login' => env('NOX_AUTH_DISCORD_REDIRECT_ROUTE', '/login'),
        ],

        'discord' => [
            'enabled' => (bool) env('NOX_AUTH_DISCORD_ENABLED', false),

            'force_discord_accounts' => (bool) env('NOX_AUTH_DISCORD_FORCE', false),

            'client_id' => env('DISCORD_CLIENT_ID'),
            'client_secret' => env('DISCORD_CLIENT_SECRET'),

            'allow_gif_avatars' => (bool) env('DISCORD_AVATAR_GIF', true),
            'avatar_default_extension' => env('DISCORD_EXTENSION_DEFAULT', 'jpg'),

            'routes' => [
                'redirect' => env('NOX_AUTH_DISCORD_REDIRECT_ROUTE', '/login/discord/redirect'),
                'callback' => env('NOX_AUTH_DISCORD_CALLBACK_ROUTE', '/login/discord'),
            ],
        ],
    ],

    'admin' => [
        'register_theme' => true,

        'register_groups' => true,
    ],

    'modules' => [
        'cache' => [
            'enabled' => true,

            'key' => 'nox.modules.all',
        ],
    ],

    'themes' => [
        'cache' => [
            'enabled' => true,

            'key' => 'nox.themes.all',
        ],
    ],
];
