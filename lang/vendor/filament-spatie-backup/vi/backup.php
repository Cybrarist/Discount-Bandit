<?php

return [

    'components' => [
        'backup_destination_list' => [
            'table' => [
                'actions' => [
                    'download' => 'Tải xuống',
                    'delete' => 'Xóa',
                ],

                'fields' => [
                    'path' => 'Đường dẫn',
                    'disk' => 'Ổ đĩa',
                    'date' => 'Ngày',
                    'size' => 'Kích thước',
                ],

                'filters' => [
                    'disk' => 'Ổ đĩa',
                ],
            ],
        ],

        'backup_destination_status_list' => [
            'table' => [
                'fields' => [
                    'name' => 'Tên',
                    'disk' => 'Ổ đĩa',
                    'healthy' => 'Sức khỏe',
                    'amount' => 'Số lượng',
                    'newest' => 'Mới nhất',
                    'used_storage' => 'Bộ nhớ đã dùng',
                ],
            ],
        ],
    ],

    'pages' => [
        'backups' => [
            'actions' => [
                'create_backup' => 'Tạo bản sao lưu',
            ],

            'heading' => 'Sao lưu',

            'messages' => [
                'backup_success' => 'Đang chạy tạo bản sao lưu dưới nền.',
            ],

            'modal' => [
                'buttons' => [
                    'only_db' => 'Chỉ DB',
                    'only_files' => 'Chỉ Tệp tin',
                    'db_and_files' => 'DB & Tệp tin',
                ],

                'label' => 'Vui lòng chọn một tùy chọn',
            ],

            'navigation' => [
                'group' => 'Cài đặt',
                'label' => 'Sao lưu',
            ],
        ],
    ],

];
