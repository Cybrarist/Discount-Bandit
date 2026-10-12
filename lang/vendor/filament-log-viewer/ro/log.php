<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Vizualizator jurnal',
    ],
    'show' => [
        'title' => 'Vizualizare jurnal :log',
    ],
    'navigation' => [
        'group' => 'Jurnale',
        'label' => 'Vizualizator jurnal',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Data',
            ],
            'level' => [
                'label' => 'Nivel',
            ],
            'message' => [
                'label' => 'Mesaj',
            ],
            'filename' => [
                'label' => 'Nume fișier',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Vizualizare',
            ],
            'download' => [
                'label' => 'Descarcă jurnal :log',
                'bulk' => [
                    'label' => 'Descarcă jurnale',
                    'error' => 'Eroare la descărcarea jurnalelor',
                ],
            ],
            'delete' => [
                'label' => 'Șterge jurnal :log',
                'success' => 'Jurnalul a fost șters cu succes',
                'error' => 'Eroare la ștergerea jurnalului',
                'bulk' => [
                    'label' => 'Șterge jurnalele selectate',
                ],
            ],
            'clear' => [
                'label' => 'Șterge jurnal :log',
                'success' => 'Jurnalul a fost șters cu succes',
                'error' => 'Eroare la ștergerea jurnalului',
                'bulk' => [
                    'success' => 'Jurnalele au fost șterse cu succes',
                    'label' => 'Șterge jurnalele selectate',
                ],
            ],
            'close' => [
                'label' => 'Înapoi',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Căutare în jurnale...',
            'go_to_top' => 'Mergi la început',
            'go_to_bottom' => 'Mergi la sfârșit',
            'top' => 'Început',
            'bottom' => 'Sfârșit',
            'no_matching_entries' => 'Nicio intrare de jurnal care să se potrivească.',
            'no_entries' => 'Nicio intrare de jurnal.',
            'context' => 'Context',
            'stack_trace' => 'Urma stivei',
        ],
        'detail' => [
            'title' => 'Detalii',
            'file_path' => 'Cale fișier',
            'log_entries' => 'Intrări',
            'size' => 'Dimensiune',
            'created_at' => 'Creat',
            'updated_at' => 'Actualizat',
        ],
    ],
    'levels' => [
        'all' => 'Toate',
        'emergency' => 'Urgență',
        'alert' => 'Avertisment',
        'critical' => 'Critic',
        'error' => 'Eroare',
        'warning' => 'Avertisment',
        'notice' => 'Notificare',
        'info' => 'Informații',
        'debug' => 'Depanare',
    ],
];
