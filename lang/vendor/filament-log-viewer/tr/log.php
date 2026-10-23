<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Günlük Görüntüleyici',
    ],
    'show' => [
        'title' => 'Günlüğü görüntüle :log',
    ],
    'navigation' => [
        'group' => 'Günlükler',
        'label' => 'Günlük Görüntüleyici',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Tarih',
            ],
            'level' => [
                'label' => 'Seviye',
            ],
            'message' => [
                'label' => 'İleti',
            ],
            'filename' => [
                'label' => 'Dosya Adı',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Görüntüle',
            ],
            'download' => [
                'label' => 'Günlüğü indir :log',
                'bulk' => [
                    'label' => 'Günlükleri indir',
                    'error' => 'Günlükleri indirirken hata oluştu',
                ],
            ],
            'delete' => [
                'label' => 'Günlüğü sil :log',
                'success' => 'Günlük başarıyla silindi',
                'error' => 'Günlüğü silerken hata oluştu',
                'bulk' => [
                    'label' => 'Seçili günlükleri sil',
                ],
            ],
            'clear' => [
                'label' => 'Günlüğü temizle :log',
                'success' => 'Günlük başarıyla temizlendi',
                'error' => 'Günlüğü temizlerken hata oluştu',
                'bulk' => [
                    'success' => 'Günlükler başarıyla temizlendi',
                    'label' => 'Seçili günlükleri temizle',
                ],
            ],
            'close' => [
                'label' => 'Geri',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Günlüklerde ara...',
            'go_to_top' => 'Başa git',
            'go_to_bottom' => 'Sona git',
            'top' => 'Başa',
            'bottom' => 'Sona',
            'no_matching_entries' => 'Eşleşen günlük girdisi yok.',
            'no_entries' => 'Günlük girdisi yok.',
            'context' => 'Bağlam',
            'stack_trace' => 'Yığın İzlemesi',
        ],
        'detail' => [
            'title' => 'Detaylar',
            'file_path' => 'Dosya Yolu',
            'log_entries' => 'Girdiler',
            'size' => 'Boyut',
            'created_at' => 'Oluşturuldu',
            'updated_at' => 'Güncellendi',
        ],
    ],
    'levels' => [
        'all' => 'Tümü',
        'emergency' => 'Acil',
        'alert' => 'Uyarı',
        'critical' => 'Kritik',
        'error' => 'Hata',
        'warning' => 'Uyarı',
        'notice' => 'Bildirim',
        'info' => 'Bilgi',
        'debug' => 'Hata Ayıklama',
    ],
];
