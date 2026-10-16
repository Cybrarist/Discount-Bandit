<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => '日誌檢視器',
    ],
    'show' => [
        'title' => '檢視日誌 :log',
    ],
    'navigation' => [
        'group' => '日誌',
        'label' => '日誌檢視器',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => '日期',
            ],
            'level' => [
                'label' => '級別',
            ],
            'message' => [
                'label' => '訊息',
            ],
            'filename' => [
                'label' => '檔案名稱',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => '檢視',
            ],
            'download' => [
                'label' => '下載日誌 :log',
                'bulk' => [
                    'label' => '下載日誌',
                    'error' => '下載日誌時出錯',
                ],
            ],
            'delete' => [
                'label' => '刪除日誌 :log',
                'success' => '日誌已成功刪除',
                'error' => '刪除日誌時出錯',
                'bulk' => [
                    'label' => '刪除已選取的日誌',
                ],
            ],
            'clear' => [
                'label' => '清空日誌 :log',
                'success' => '日誌已成功清空',
                'error' => '清空日誌時出錯',
                'bulk' => [
                    'success' => '日誌已成功清空',
                    'label' => '清空已選取的日誌',
                ],
            ],
            'close' => [
                'label' => '返回',
            ],
        ],
        'modal' => [
            'search_placeholder' => '搜尋日誌...',
            'go_to_top' => '移至頂部',
            'go_to_bottom' => '移至底部',
            'top' => '頂部',
            'bottom' => '底部',
            'no_matching_entries' => '沒有相符的日誌項目。',
            'no_entries' => '沒有日誌項目。',
            'context' => '內容',
            'stack_trace' => '堆疊追蹤',
        ],
        'detail' => [
            'title' => '詳細資料',
            'file_path' => '檔案路徑',
            'log_entries' => '項目',
            'size' => '大小',
            'created_at' => '建立時間',
            'updated_at' => '更新時間',
        ],
    ],
    'levels' => [
        'all' => '全部',
        'emergency' => '緊急',
        'alert' => '警告',
        'critical' => '嚴重',
        'error' => '錯誤',
        'warning' => '警告',
        'notice' => '通知',
        'info' => '資訊',
        'debug' => '偵錯',
    ],
];
