<?php

return [

    'components' => [
        'backup_destination_list' => [
            'table' => [
                'actions' => [
                    'download' => 'Download',
                    'delete' => 'Delete',
                ],

                'fields' => [
                    'path' => 'Path',
                    'disk' => 'Disk',
                    'date' => 'Date',
                    'size' => 'Size',
                ],

                'filters' => [
                    'disk' => 'Disk',
                ],
            ],
        ],

        'backup_destination_status_list' => [
            'table' => [
                'fields' => [
                    'name' => 'Name',
                    'disk' => 'Disk',
                    'healthy' => 'Healthy',
                    'amount' => 'Amount',
                    'newest' => 'Newest',
                    'used_storage' => 'Used Storage',
                ],
            ],
        ],
    ],

    'pages' => [
        'backups' => [
            'actions' => [
                'create_backup' => 'Create Backup',
            ],

            'heading' => 'Backups',

            'messages' => [
                'backup_success' => 'Creating a new backup in background.',
                'backup_delete_success' => 'Deleting this backup in background.',
            ],

            'modal' => [
                'buttons' => [
                    'only_db' => 'Only DB',
                    'only_files' => 'Only Files',
                    'db_and_files' => 'DB & Files',
                ],

                'label' => 'Please choose an option',
            ],

            'navigation' => [
                'group' => 'Settings',
                'label' => 'Backups',
            ],
        ],
    ],

];
