<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => '로그 뷰어',
    ],
    'show' => [
        'title' => '로그 보기 :log',
    ],
    'navigation' => [
        'group' => '로그',
        'label' => '로그 뷰어',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => '날짜',
            ],
            'level' => [
                'label' => '수준',
            ],
            'message' => [
                'label' => '메시지',
            ],
            'filename' => [
                'label' => '파일명',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => '보기',
            ],
            'download' => [
                'label' => '로그 다운로드 :log',
                'bulk' => [
                    'label' => '로그 다운로드',
                    'error' => '로그 다운로드 중 오류 발생',
                ],
            ],
            'delete' => [
                'label' => '로그 삭제 :log',
                'success' => '로그가 성공적으로 삭제되었습니다',
                'error' => '로그 삭제 중 오류 발생',
                'bulk' => [
                    'label' => '선택된 로그 삭제',
                ],
            ],
            'clear' => [
                'label' => '로그 지우기 :log',
                'success' => '로그가 성공적으로 지워졌습니다',
                'error' => '로그 지우기 중 오류 발생',
                'bulk' => [
                    'success' => '로그가 성공적으로 지워졌습니다',
                    'label' => '선택된 로그 지우기',
                ],
            ],
            'close' => [
                'label' => '돌아가기',
            ],
        ],
        'modal' => [
            'search_placeholder' => '로그 검색...',
            'go_to_top' => '맨 위로',
            'go_to_bottom' => '맨 아래로',
            'top' => '위',
            'bottom' => '아래',
            'no_matching_entries' => '일치하는 로그 항목이 없습니다.',
            'no_entries' => '로그 항목이 없습니다.',
            'context' => '컨텍스트',
            'stack_trace' => '스택 추적',
        ],
        'detail' => [
            'title' => '상세정보',
            'file_path' => '파일 경로',
            'log_entries' => '항목',
            'size' => '크기',
            'created_at' => '생성일',
            'updated_at' => '수정일',
        ],
    ],
    'levels' => [
        'all' => '모두',
        'emergency' => '긴급',
        'alert' => '알림',
        'critical' => '치명적',
        'error' => '오류',
        'warning' => '경고',
        'notice' => '공지',
        'info' => '정보',
        'debug' => '디버그',
    ],
];
