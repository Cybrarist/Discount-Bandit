<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'ログビューア',
    ],
    'show' => [
        'title' => 'ログを表示 :log',
    ],
    'navigation' => [
        'group' => 'ログ',
        'label' => 'ログビューア',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => '日付',
            ],
            'level' => [
                'label' => 'レベル',
            ],
            'message' => [
                'label' => 'メッセージ',
            ],
            'filename' => [
                'label' => 'ファイル名',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => '表示',
            ],
            'download' => [
                'label' => 'ログをダウンロード :log',
                'bulk' => [
                    'label' => 'ログをダウンロード',
                    'error' => 'ログのダウンロード中にエラーが発生しました',
                ],
            ],
            'delete' => [
                'label' => 'ログを削除 :log',
                'success' => 'ログを削除しました',
                'error' => 'ログの削除中にエラーが発生しました',
                'bulk' => [
                    'label' => '選択したログを削除',
                ],
            ],
            'clear' => [
                'label' => 'ログをクリア :log',
                'success' => 'ログをクリアしました',
                'error' => 'ログのクリア中にエラーが発生しました',
                'bulk' => [
                    'success' => 'ログをクリアしました',
                    'label' => '選択したログをクリア',
                ],
            ],
            'close' => [
                'label' => '戻る',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'ログを検索...',
            'go_to_top' => '先頭へ移動',
            'go_to_bottom' => '末尾へ移動',
            'top' => '先頭',
            'bottom' => '末尾',
            'no_matching_entries' => '一致するログエントリがありません。',
            'no_entries' => 'ログエントリがありません。',
            'context' => 'コンテキスト',
            'stack_trace' => 'スタックトレース',
        ],
        'detail' => [
            'title' => '詳細',
            'file_path' => 'ファイルパス',
            'log_entries' => 'エントリ数',
            'size' => 'サイズ',
            'created_at' => '作成日時',
            'updated_at' => '更新日時',
        ],
    ],
    'levels' => [
        'all' => 'すべて',
        'emergency' => '緊急',
        'alert' => '警報',
        'critical' => '重大',
        'error' => 'エラー',
        'warning' => '警告',
        'notice' => '注意',
        'info' => '情報',
        'debug' => 'デバッグ',
    ],
];
