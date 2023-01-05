<?php

return [
    'not_found' => 'Module cannot be found',

    'boot_failed' => 'Module failed to boot',

    'publish' => [
        'success' => [
            'title' => ':name',
            'body' => 'Successfully published module',
        ],

        'failed' => [
            'title' => ':name',
            'body' => 'Failed to publish module',
        ],
    ],

    'enable' => [
        'success' => [
            'title' => ':name',
            'body' => 'Successfully enabled module',
        ],

        'failed' => [
            'title' => ':name',
        ],
    ],

    'disable' => [
        'success' => [
            'title' => ':name',
            'body' => 'Successfully disabled module',
        ],

        'failed' => [
            'title' => ':name',
        ],
    ],

    'install' => [
        'success' => [
            'title' => ':name',
            'body' => 'Successfully installed module',
        ],

        'failed' => [
            'title' => 'Failed to install module',
            'files_not_found' => 'Failed to find installation files',
            'manifest_not_found' => 'Failed to find module manifest',
            'manifest_load_failed' => 'Failed to load module manifest',
            'invalid_manifest' => 'Invalid module manifest',
            'already_installed' => 'Module already installed',
            'extract_failed' => 'Failed to extract module',
        ],
    ],

    'delete' => [
        'success' => [
            'title' => ':name',
            'body' => 'Successfully deleted module',
        ],

        'failed' => [
            'title' => 'Failed to delete module',
        ],
    ],
];
