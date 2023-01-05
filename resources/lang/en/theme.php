<?php

return [
    'not_found' => 'Theme cannot be found',

    'boot_failed' => 'Theme failed to boot',

    'publish' => [
        'success' => [
            'title' => ':name',
            'body' => 'Successfully published theme',
        ],

        'failed' => [
            'title' => ':name',
            'body' => 'Failed to publish theme',
        ],
    ],

    'enable' => [
        'success' => [
            'title' => ':name',
            'body' => 'Successfully enabled theme',
        ],

        'failed' => [
            'title' => ':name',
        ],
    ],

    'disable' => [
        'success' => [
            'title' => ':name',
            'body' => 'Successfully disabled theme',
        ],

        'failed' => [
            'title' => ':name',
        ],
    ],

    'install' => [
        'success' => [
            'title' => ':name',
            'body' => 'Successfully installed theme',
        ],

        'failed' => [
            'title' => 'Failed to install theme',
            'files_not_found' => 'Failed to find installation files',
            'manifest_not_found' => 'Failed to find theme manifest',
            'manifest_load_failed' => 'Failed to load theme manifest',
            'invalid_manifest' => 'Invalid theme manifest',
            'already_installed' => 'Theme already installed',
            'extract_failed' => 'Failed to extract theme',
            'parent_not_found' => 'Failed to find parent theme',
        ],
    ],

    'delete' => [
        'success' => [
            'title' => ':name',
            'body' => 'Successfully deleted theme',
        ],

        'failed' => [
            'title' => 'Failed to delete theme',
        ],
    ],
];
