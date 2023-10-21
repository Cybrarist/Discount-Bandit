<?php

return [

    'components' => [
        'backup_destination_list' => [
            'table' => [
                'actions' => [
                    'download' => 'İndir',
                    'delete' => 'Sil',
                ],

                'fields' => [
                    'path' => 'Yol',
                    'disk' => 'Disk',
                    'date' => 'Tarih',
                    'size' => 'Boyut',
                ],

                'filters' => [
                    'disk' => 'Disk',
                ],
            ],
        ],

        'backup_destination_status_list' => [
            'table' => [
                'fields' => [
                    'name' => 'Ad',
                    'disk' => 'Disk',
                    'healthy' => 'Sağlık',
                    'amount' => 'Adet',
                    'newest' => 'Zaman',
                    'used_storage' => 'Kullanılan Depolama',
                ],
            ],
        ],
    ],

    'pages' => [
        'backups' => [
            'actions' => [
                'create_backup' => 'Yedek Oluştur',
            ],

            'heading' => 'Yedekler',

            'messages' => [
                'backup_success' => 'Arka planda yeni bir yedek oluşturuluyor.',
            ],

            'modal' => [
                'buttons' => [
                    'only_db' => 'Sadece Veri Tabanı',
                    'only_files' => 'Sadece Dosyalar',
                    'db_and_files' => 'Veri Tabanı & Dosyalar',
                ],

                'label' => 'Bir seçenek seçin',
            ],

            'navigation' => [
                'group' => 'Ayarlar',
                'label' => 'Yedekler',
            ],
        ],
    ],

];
