<?php

return [

    'components' => [
        'backup_destination_list' => [
            'table' => [
                'actions' => [
                    'download' => 'Download',
                    'delete' => 'Verwijderen',
                ],

                'fields' => [
                    'path' => 'Pad',
                    'disk' => 'Disk',
                    'date' => 'Datum',
                    'size' => 'Grootte',
                ],

                'filters' => [
                    'disk' => 'Disk',
                ],
            ],
        ],

        'backup_destination_status_list' => [
            'table' => [
                'fields' => [
                    'name' => 'Naam',
                    'disk' => 'Disk',
                    'healthy' => 'Gezond',
                    'amount' => 'Aantal',
                    'newest' => 'Laatste',
                    'used_storage' => 'Gebruikte opslag',
                ],
            ],
        ],
    ],

    'pages' => [
        'backups' => [
            'actions' => [
                'create_backup' => 'Backup maken',
            ],

            'heading' => 'Backups',

            'messages' => [
                'backup_success' => 'Backup maken in de achtergrond.',
                'backup_delete_success' => 'Backup verwijderen in de achtergrond.',
            ],

            'modal' => [
                'buttons' => [
                    'only_db' => 'Alleen DB',
                    'only_files' => 'Alleen bestanden',
                    'db_and_files' => 'DB en bestanden',
                ],

                'label' => 'Kies een optie',
            ],

            'navigation' => [
                'group' => 'Instellingen',
                'label' => 'Backups',
            ],
        ],
    ],

];
