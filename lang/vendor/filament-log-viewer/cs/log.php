<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Prohlížeč protokolů',
    ],
    'show' => [
        'title' => 'Zobrazit protokol :log',
    ],
    'navigation' => [
        'group' => 'Protokoly',
        'label' => 'Prohlížeč protokolů',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Datum',
            ],
            'level' => [
                'label' => 'Úroveň',
            ],
            'message' => [
                'label' => 'Zpráva',
            ],
            'filename' => [
                'label' => 'Název souboru',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Zobrazit',
            ],
            'download' => [
                'label' => 'Stáhnout protokol :log',
                'bulk' => [
                    'label' => 'Stáhnout protokoly',
                    'error' => 'Chyba při stahování protokolů',
                ],
            ],
            'delete' => [
                'label' => 'Odstranit protokol :log',
                'success' => 'Protokol byl úspěšně odstraněn',
                'error' => 'Chyba při odstraňování protokolu',
                'bulk' => [
                    'label' => 'Odstranit vybrané protokoly',
                ],
            ],
            'clear' => [
                'label' => 'Vymazat protokol :log',
                'success' => 'Protokol byl úspěšně vymazán',
                'error' => 'Chyba při vymazávání protokolu',
                'bulk' => [
                    'success' => 'Protokoly byly úspěšně vymazány',
                    'label' => 'Vymazat vybrané protokoly',
                ],
            ],
            'close' => [
                'label' => 'Zpět',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Hledat protokoly...',
            'go_to_top' => 'Na vrchol',
            'go_to_bottom' => 'Na dno',
            'top' => 'Vrchol',
            'bottom' => 'Dno',
            'no_matching_entries' => 'Žádné odpovídající záznamy protokolu.',
            'no_entries' => 'Žádné záznamy protokolu.',
            'context' => 'Kontext',
            'stack_trace' => 'Trasování zásobníku',
        ],
        'detail' => [
            'title' => 'Podrobnosti',
            'file_path' => 'Cesta souboru',
            'log_entries' => 'Položky',
            'size' => 'Velikost',
            'created_at' => 'Vytvořeno',
            'updated_at' => 'Aktualizováno',
        ],
    ],
    'levels' => [
        'all' => 'Všechny',
        'emergency' => 'Nouzový stav',
        'alert' => 'Výstraha',
        'critical' => 'Kritické',
        'error' => 'Chyba',
        'warning' => 'Upozornění',
        'notice' => 'Upozornění',
        'info' => 'Informace',
        'debug' => 'Ladění',
    ],
];
