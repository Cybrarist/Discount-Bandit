<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Prehliadač protokolov',
    ],
    'show' => [
        'title' => 'Zobraziť protokol :log',
    ],
    'navigation' => [
        'group' => 'Protokoly',
        'label' => 'Prehliadač protokolov',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Dátum',
            ],
            'level' => [
                'label' => 'Úroveň',
            ],
            'message' => [
                'label' => 'Správa',
            ],
            'filename' => [
                'label' => 'Názov súboru',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Zobraziť',
            ],
            'download' => [
                'label' => 'Stiahnuť protokol :log',
                'bulk' => [
                    'label' => 'Stiahnuť protokoly',
                    'error' => 'Chyba pri sťahovaní protokolov',
                ],
            ],
            'delete' => [
                'label' => 'Odstrániť protokol :log',
                'success' => 'Protokol bol úspešne odstránený',
                'error' => 'Chyba pri odstraňovaní protokolu',
                'bulk' => [
                    'label' => 'Odstrániť vybrané protokoly',
                ],
            ],
            'clear' => [
                'label' => 'Vymazať protokol :log',
                'success' => 'Protokol bol úspešne vymazaný',
                'error' => 'Chyba pri vymazávaní protokolu',
                'bulk' => [
                    'success' => 'Protokoly boli úspešne vymazané',
                    'label' => 'Vymazať vybrané protokoly',
                ],
            ],
            'close' => [
                'label' => 'Späť',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Hľadať protokoly...',
            'go_to_top' => 'Ísť na vrch',
            'go_to_bottom' => 'Ísť na spodok',
            'top' => 'Vrch',
            'bottom' => 'Spodok',
            'no_matching_entries' => 'Žiadne zodpovedajúce záznamy v protokole.',
            'no_entries' => 'Žiadne záznamy v protokole.',
            'context' => 'Kontext',
            'stack_trace' => 'Trasovanie zásobníka',
        ],
        'detail' => [
            'title' => 'Podrobnosti',
            'file_path' => 'Cesta súboru',
            'log_entries' => 'Položky',
            'size' => 'Veľkosť',
            'created_at' => 'Vytvorené',
            'updated_at' => 'Aktualizované',
        ],
    ],
    'levels' => [
        'all' => 'Všetko',
        'emergency' => 'Núdzový stav',
        'alert' => 'Výstraha',
        'critical' => 'Kritické',
        'error' => 'Chyba',
        'warning' => 'Upozornenie',
        'notice' => 'Upozornenie',
        'info' => 'Informácia',
        'debug' => 'Ladenie',
    ],
];
