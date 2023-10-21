<?php

return [

    'components' => [
        'backup_destination_list' => [
            'table' => [
                'actions' => [
                    'download' => 'Télécharger',
                    'delete' => 'Supprimer',
                ],

                'fields' => [
                    'path' => 'Chemin d\'accès',
                    'disk' => 'Disque',
                    'date' => 'Date',
                    'size' => 'Taille',
                ],

                'filters' => [
                    'disk' => 'Disque',
                ],
            ],
        ],

        'backup_destination_status_list' => [
            'table' => [
                'fields' => [
                    'name' => 'Nom',
                    'disk' => 'Disque',
                    'healthy' => 'Statut',
                    'amount' => 'Montant',
                    'newest' => 'Plus récent',
                    'used_storage' => 'Stockage utilisé',
                ],
            ],
        ],
    ],

    'pages' => [
        'backups' => [
            'actions' => [
                'create_backup' => 'Créer une sauvegarde',
            ],

            'heading' => 'Sauvegardes',

            'messages' => [
                'backup_success' => 'Création d\'une nouvelle sauvegarde en arrière-plan.',
            ],

            'modal' => [
                'buttons' => [
                    'only_db' => 'Seulement la base de données',
                    'only_files' => 'Seulement les fichiers',
                    'db_and_files' => 'Base de données & Fichiers',
                ],

                'label' => 'Veuillez choisir une option',
            ],

            'navigation' => [
                'group' => 'Paramètres',
                'label' => 'Sauvegardes',
            ],
        ],
    ],

];
