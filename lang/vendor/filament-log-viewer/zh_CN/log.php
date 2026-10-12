<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => '日志查看器',
    ],
    'show' => [
        'title' => '查看日志 :log',
    ],
    'navigation' => [
        'group' => '日志',
        'label' => '日志查看器',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => '日期',
            ],
            'level' => [
                'label' => '级别',
            ],
            'message' => [
                'label' => '消息',
            ],
            'filename' => [
                'label' => '文件名',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => '查看',
            ],
            'download' => [
                'label' => '下载日志 :log',
                'bulk' => [
                    'label' => '下载日志',
                    'error' => '下载日志出错',
                ],
            ],
            'delete' => [
                'label' => '删除日志 :log',
                'success' => '日志已成功删除',
                'error' => '删除日志出错',
                'bulk' => [
                    'label' => '删除选中日志',
                ],
            ],
            'clear' => [
                'label' => '清空日志 :log',
                'success' => '日志已成功清空',
                'error' => '清空日志出错',
                'bulk' => [
                    'success' => '日志已成功清空',
                    'label' => '清空选中日志',
                ],
            ],
            'close' => [
                'label' => '返回',
            ],
        ],
        'modal' => [
            'search_placeholder' => '搜索日志...',
            'go_to_top' => '转到顶部',
            'go_to_bottom' => '转到底部',
            'top' => '顶部',
            'bottom' => '底部',
            'no_matching_entries' => '没有匹配的日志项。',
            'no_entries' => '没有日志项。',
            'context' => '上下文',
            'stack_trace' => '堆栈跟踪',
        ],
        'detail' => [
            'title' => '详情',
            'file_path' => '文件路径',
            'log_entries' => '项目',
            'size' => '大小',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ],
    ],
    'levels' => [
        'all' => '全部',
        'emergency' => '紧急',
        'alert' => '警报',
        'critical' => '严重',
        'error' => '错误',
        'warning' => '警告',
        'notice' => '通知',
        'info' => '信息',
        'debug' => '调试',
    ],
];
