<?php

return [

    'components' => [
        'backup_destination_list' => [
            'table' => [
                'actions' => [
                    'download' => 'Скачать',
                    'delete' => 'Удалить',
                ],

                'fields' => [
                    'path' => 'Путь',
                    'disk' => 'Диск',
                    'date' => 'Дата',
                    'size' => 'Размер',
                ],

                'filters' => [
                    'disk' => 'Диск',
                ],
            ],
        ],

        'backup_destination_status_list' => [
            'table' => [
                'fields' => [
                    'name' => 'Имя',
                    'disk' => 'Диск',
                    'healthy' => 'Исправен',
                    'amount' => 'Количество',
                    'newest' => 'Последний',
                    'used_storage' => 'Объём в хранилище',
                ],
            ],
        ],
    ],

    'pages' => [
        'backups' => [
            'actions' => [
                'create_backup' => 'Создать резервную копию',
            ],

            'heading' => 'Резервные копии',

            'messages' => [
                'backup_success' => 'Создание новой резервной копии в фоновом режиме.',
                'backup_delete_success' => 'Удаление этой резервной копии в фоновом режиме.',
            ],

            'modal' => [
                'buttons' => [
                    'only_db' => 'Только база',
                    'only_files' => 'Только файлы',
                    'db_and_files' => 'База и файлы',
                ],

                'label' => 'Пожалуйста, выберите опцию',
            ],

            'navigation' => [
                'group' => 'Настройки',
                'label' => 'Резервные копии',
            ],
        ],
    ],

];
