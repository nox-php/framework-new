<?php

return [
    'groups' => [
        'appearance' => 'Appearance',
        'extend' => 'Extend',
        'system' => 'System',
    ],

    'resources' => [
        'theme' => [
            'navigation_label' => 'Themes',
            'label' => 'Theme',

            'actions' => [
                'install' => 'Install themes',
                'enable' => 'Enable',
                'disable' => 'Disable',
            ],

            'form' => [
                'inputs' => [
                    'name' => 'Name',
                    'version' => 'Version',
                    'path' => 'Path',
                    'description' => 'Description',
                    'parent' => 'Parent theme',
                ],
            ],

            'table' => [
                'columns' => [
                    'name' => 'Name',
                    'description' => 'Description',
                    'version' => 'Version',
                    'status' => [
                        'label' => 'Status',

                        'enum' => [
                            'enabled' => 'Enabled',
                            'disabled' => 'Disabled',
                        ],
                    ],
                ],

                'actions' => [
                    'enable' => 'Enable',
                    'disable' => 'Disable',
                ],
            ],
        ],

        'module' => [
            'navigation_label' => 'Modules',
            'label' => 'Module',

            'actions' => [
                'install' => 'Install modules',
                'enable' => 'Enable',
                'disable' => 'Disable',
            ],

            'form' => [
                'inputs' => [
                    'name' => 'Name',
                    'version' => 'Version',
                    'path' => 'Path',
                    'description' => 'Description',
                ],
            ],

            'table' => [
                'columns' => [
                    'name' => 'Name',
                    'description' => 'Description',
                    'version' => 'Version',
                    'status' => [
                        'label' => 'Status',

                        'enum' => [
                            'enabled' => 'Enabled',
                            'disabled' => 'Disabled',
                        ],
                    ],
                ],

                'actions' => [
                    'enable' => 'Enable',
                    'disable' => 'Disable',
                ],

                'bulk_actions' => [
                    'enable' => 'Enable selected',
                    'disable' => 'Disable selected',
                ],
            ],
        ],

        'activity' => [
            'navigation_label' => 'Activities',
            'label' => 'Activity',

            'form' => [
                'inputs' => [
                    'causer_type' => 'Causer type',
                    'causer_id' => 'Causer ID',
                    'subject_type' => 'Subject type',
                    'subject_id' => 'Subject ID',
                    'description' => 'Description',
                    'properties' => 'Properties',
                    'before' => [
                        'label' => 'Before',
                        'helper' => 'Old model attributes',
                    ],
                    'after' => [
                        'label' => 'After',
                        'helper' => 'New model attributes',
                    ],
                ],
            ],

            'table' => [
                'columns' => [
                    'description' => 'Description',
                    'subject' => 'Subject',
                    'created_at' => 'Created at',
                ],
            ],
        ],

        'user' => [
            'navigation_label' => 'Users',
            'label' => 'User',

            'form' => [
                'inputs' => [
                    'username' => 'Username',
                    'email' => 'Email address',
                    'roles' => 'Roles',
                    'discord_name' => 'Discord name',
                    'created_at' => 'Created at',
                    'updated_at' => 'Updated at',
                ],
            ],

            'table' => [
                'columns' => [
                    'avatar' => 'Avatar',
                    'username' => 'Username',
                    'email' => 'Email address',
                    'discord_name' => 'Discord name',
                    'created_at' => 'Created at',
                    'updated_at' => 'Updated at',
                ],
            ],
        ],
    ],

    'pages' => [
        'health' => [
            'label' => 'System Health',

            'last_updated' => 'Last updated',

            'actions' => [
                'refresh' => 'Refresh',
            ],
        ],

        'settings' => [
            'label' => 'Settings',

            'new_update' => 'A new update for Nox is available to install (v:version)',

            'actions' => [
                'check_for_updates' => 'Check for updates',
                'save' => 'Save',
                'cancel' => 'Cancel',
                'install' => 'Install',
            ],

            'form' => [
                'tabs' => [
                    'site' => 'Site',
                    'database' => 'Database',
                    'discord' => 'Discord',
                    'mail' => 'Mail',
                ],

                'fieldsets' => [
                    'global' => 'Global',
                    'debugging' => 'Debugging',
                    'credentials' => 'Credentials',
                    'signature' => 'Signature',
                ],

                'inputs' => [
                    'site_name' => [
                        'label' => 'Site name',
                        'hint' => 'Updating this will sign everyone out',
                    ],
                    'site_url' => [
                        'label' => 'Site URL',
                        'hint' => 'Updating this will sign everyone out',
                    ],
                    'site_environment' => [
                        'label' => 'Environment',
                        'options' => [
                            'production' => 'Production',
                            'testing' => 'Testing',
                            'local' => 'Local',
                        ],
                    ],
                    'site_debug' => [
                        'label' => 'Enable debug mode',
                        'helper' => 'This should never be enabled in production',
                    ],

                    'database_driver' => [
                        'label' => 'Driver',
                        'options' => [
                            'mysql' => 'mysql',
                            'pgsql' => 'pgsql',
                            'sqlsrv' => 'sqlsrv',
                            'sqlite' => 'sqlite',
                        ],
                    ],
                    'database_host' => 'Host',
                    'database_port' => 'Port',
                    'database_database' => 'Database',
                    'database_username' => 'Username',
                    'database_password' => 'Password',

                    'discord_client_id' => 'Client ID',
                    'discord_client_secret' => 'Client secret',

                    'mail_transport' => [
                        'label' => 'Driver',
                        'options' => [
                            'smtp' => 'smtp',
                            'sendmail' => 'sendmail',
                        ],
                    ],
                    'mail_path' => 'Path',
                    'mail_host' => 'Host',
                    'mail_port' => 'Port',
                    'mail_username' => 'Username',
                    'mail_password' => 'Password',
                    'mail_encryption' => 'Encryption',
                    'mail_from_address' => 'Sender address',
                    'mail_from_name' => 'Sender name',
                ],
            ],
        ],
    ],

    'notifications' => [
        'nox_update' => [
            'success' => [
                'title' => 'Successfully updated Nox',
                'body' => 'Nox has been updated from :old_version to :new_version',

                'actions' => [
                    'view_log' => 'View log',
                ],
            ],

            'failed' => [
                'title' => 'Failed to update Nox',
                'body' => 'Nox :new_version has failed to install, reverting back to :old_version',

                'actions' => [
                    'retry' => 'Retry',
                    'view_log' => 'View log',
                ],
            ],

            'install' => [
                'title' => 'A new version of Nox is available to install',
                'body' => 'Nox :new_version is available, currently installed :old_version',

                'actions' => [
                    'install' => 'Install',
                ],
            ],

            'updating' => [
                'title' => 'Nox :version is updating in the background',
                'body' => 'You will be notified once it has finished',
            ],
        ],

        'settings' => [
            'success' => [
                'title' => 'Successfully updated settings',
            ],

            'failed' => [
                'database' => [
                    'title' => 'Failed to update settings',
                    'body' => 'Failed to connect to the database',
                ],

                'config' => [
                    'title' => 'Failed to update settings',
                    'body' => 'Failed to update config file',
                ],
            ],

            'check_updates' => [
                'title' => 'Checking for Nox updates in the background',
                'body' => 'You will be notified if an update is available',
            ],
        ],
    ],
];
