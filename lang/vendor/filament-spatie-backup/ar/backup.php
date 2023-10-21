<?php

return [

    'components' => [
        'backup_destination_list' => [
            'table' => [
                'actions' => [
                    'download' => 'تحميل',
                    'delete' => 'حذف',
                ],

                'fields' => [
                    'path' => 'المسار',
                    'disk' => 'قرص التخزين',
                    'date' => 'التاريخ',
                    'size' => 'الحجم',
                ],

                'filters' => [
                    'disk' => 'قرص التخزين',
                ],
            ],
        ],

        'backup_destination_status_list' => [
            'table' => [
                'fields' => [
                    'name' => 'الإسم',
                    'disk' => 'قرص التخزين',
                    'healthy' => 'الحالة',
                    'amount' => 'السعة',
                    'newest' => 'الأحدث',
                    'used_storage' => 'السعة المستخدمة',
                ],
            ],
        ],
    ],

    'pages' => [
        'backups' => [
            'actions' => [
                'create_backup' => 'إنشاء نسخة إحتياطية',
            ],

            'heading' => 'النسخ الإحتياطي',

            'messages' => [
                'backup_success' => 'إنشاء نسخة إحتياطية في الخلفية.',
            ],

            'modal' => [
                'buttons' => [
                    'only_db' => 'قاعدة البيانات',
                    'only_files' => 'الملفات',
                    'db_and_files' => 'الملفات والقاعدة',
                ],

                'label' => 'الرجاء الاختيار لإنشاء نسخة إحتياطية',
            ],

            'navigation' => [
                'group' => 'إعدادات',
                'label' => 'النسخ الإحتياطي',
            ],
        ],
    ],

];
