<?php

return [

    'components' => [
        'backup_destination_list' => [
            'table' => [
                'actions' => [
                    'download' => 'Descargar',
                    'delete' => 'Eliminar',
                ],

                'fields' => [
                    'path' => 'Ruta',
                    'disk' => 'Disco',
                    'date' => 'Fecha',
                    'size' => 'Tamaño',
                ],

                'filters' => [
                    'disk' => 'Disco',
                ],
            ],
        ],

        'backup_destination_status_list' => [
            'table' => [
                'fields' => [
                    'name' => 'Nombre',
                    'disk' => 'Disco',
                    'healthy' => 'Estado',
                    'amount' => 'Cantidad',
                    'newest' => 'Más reciente',
                    'used_storage' => 'Espacio utilizado',
                ],
            ],
        ],
    ],

    'pages' => [
        'backups' => [
            'actions' => [
                'create_backup' => 'Hacer respaldo',
            ],

            'heading' => 'Respaldos',

            'messages' => [
                'backup_success' => 'Creando un nuevo respaldo en segundo plano.',
            ],

            'modal' => [
                'buttons' => [
                    'only_db' => 'Solo la base de datos',
                    'only_files' => 'Solo los archivos',
                    'db_and_files' => 'Base de datos y archivos',
                ],

                'label' => 'Elija una opción',
            ],

            'navigation' => [
                'group' => 'Configuraciones',
                'label' => 'Respaldos',
            ],
        ],
    ],

];
