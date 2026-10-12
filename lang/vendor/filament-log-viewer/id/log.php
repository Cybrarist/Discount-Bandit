<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Penampil Log',
    ],
    'show' => [
        'title' => 'Lihat log :log',
    ],
    'navigation' => [
        'group' => 'Log',
        'label' => 'Penampil Log',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Tanggal',
            ],
            'level' => [
                'label' => 'Tingkat',
            ],
            'message' => [
                'label' => 'Pesan',
            ],
            'filename' => [
                'label' => 'Nama File',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Lihat',
            ],
            'download' => [
                'label' => 'Unduh log :log',
                'bulk' => [
                    'label' => 'Unduh log',
                    'error' => 'Kesalahan saat mengunduh log',
                ],
            ],
            'delete' => [
                'label' => 'Hapus log :log',
                'success' => 'Log berhasil dihapus',
                'error' => 'Kesalahan saat menghapus log',
                'bulk' => [
                    'label' => 'Hapus log yang dipilih',
                ],
            ],
            'clear' => [
                'label' => 'Kosongkan log :log',
                'success' => 'Log berhasil dikosongkan',
                'error' => 'Kesalahan saat mengosongkan log',
                'bulk' => [
                    'success' => 'Log berhasil dikosongkan',
                    'label' => 'Kosongkan log yang dipilih',
                ],
            ],
            'close' => [
                'label' => 'Kembali',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Cari log...',
            'go_to_top' => 'Ke atas',
            'go_to_bottom' => 'Ke bawah',
            'top' => 'Atas',
            'bottom' => 'Bawah',
            'no_matching_entries' => 'Tidak ada entri log yang sesuai.',
            'no_entries' => 'Tidak ada entri log.',
            'context' => 'Konteks',
            'stack_trace' => 'Jejak Stack',
        ],
        'detail' => [
            'title' => 'Detail',
            'file_path' => 'Jalur File',
            'log_entries' => 'Entri',
            'size' => 'Ukuran',
            'created_at' => 'Dibuat',
            'updated_at' => 'Diperbarui',
        ],
    ],
    'levels' => [
        'all' => 'Semua',
        'emergency' => 'Darurat',
        'alert' => 'Peringatan',
        'critical' => 'Kritis',
        'error' => 'Kesalahan',
        'warning' => 'Peringatan',
        'notice' => 'Pemberitahuan',
        'info' => 'Info',
        'debug' => 'Debug',
    ],
];
