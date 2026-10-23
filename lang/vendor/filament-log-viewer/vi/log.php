<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Trình xem nhật ký',
    ],
    'show' => [
        'title' => 'Xem nhật ký :log',
    ],
    'navigation' => [
        'group' => 'Nhật ký',
        'label' => 'Trình xem nhật ký',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Ngày',
            ],
            'level' => [
                'label' => 'Mức độ',
            ],
            'message' => [
                'label' => 'Tin nhắn',
            ],
            'filename' => [
                'label' => 'Tên tệp',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Xem',
            ],
            'download' => [
                'label' => 'Tải nhật ký :log',
                'bulk' => [
                    'label' => 'Tải nhật ký',
                    'error' => 'Lỗi tải nhật ký',
                ],
            ],
            'delete' => [
                'label' => 'Xóa nhật ký :log',
                'success' => 'Nhật ký đã xóa thành công',
                'error' => 'Lỗi xóa nhật ký',
                'bulk' => [
                    'label' => 'Xóa nhật ký được chọn',
                ],
            ],
            'clear' => [
                'label' => 'Xóa sạch nhật ký :log',
                'success' => 'Nhật ký đã xóa sạch thành công',
                'error' => 'Lỗi xóa sạch nhật ký',
                'bulk' => [
                    'success' => 'Nhật ký đã xóa sạch thành công',
                    'label' => 'Xóa sạch nhật ký được chọn',
                ],
            ],
            'close' => [
                'label' => 'Quay lại',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Tìm kiếm nhật ký...',
            'go_to_top' => 'Đi đến đầu',
            'go_to_bottom' => 'Đi đến cuối',
            'top' => 'Đầu',
            'bottom' => 'Cuối',
            'no_matching_entries' => 'Không có mục nhật ký phù hợp.',
            'no_entries' => 'Không có mục nhật ký.',
            'context' => 'Bối cảnh',
            'stack_trace' => 'Theo dõi ngăn xếp',
        ],
        'detail' => [
            'title' => 'Chi tiết',
            'file_path' => 'Đường dẫn tệp',
            'log_entries' => 'Mục nhập',
            'size' => 'Kích cỡ',
            'created_at' => 'Tạo ngày',
            'updated_at' => 'Cập nhật ngày',
        ],
    ],
    'levels' => [
        'all' => 'Tất cả',
        'emergency' => 'Khẩn cấp',
        'alert' => 'Cảnh báo',
        'critical' => 'Quan trọng',
        'error' => 'Lỗi',
        'warning' => 'Cảnh báo',
        'notice' => 'Thông báo',
        'info' => 'Thông tin',
        'debug' => 'Gỡ lỗi',
    ],
];
