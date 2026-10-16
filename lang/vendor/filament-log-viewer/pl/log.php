<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Przegląd logów',
    ],
    'show' => [
        'title' => 'Podgląd logu :log',
    ],
    'navigation' => [
        'group' => 'System',
        'label' => 'Logi systemowe',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Data',
            ],
            'level' => [
                'label' => 'Poziom',
            ],
            'message' => [
                'label' => 'Wiadomość',
            ],
            'filename' => [
                'label' => 'Nazwa pliku',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Podgląd',
            ],
            'download' => [
                'label' => 'Pobierz log :log',
                'bulk' => [
                    'label' => 'Pobierz logi',
                    'error' => 'Błąd podczas pobierania logów',
                ],
            ],
            'delete' => [
                'label' => 'Usuń log :log',
                'success' => 'Log został pomyślnie usunięty',
                'error' => 'Błąd podczas usuwania logu',
                'bulk' => [
                    'label' => 'Usuń zaznaczone logi',
                ],
            ],
            'clear' => [
                'label' => 'Wyczyść log :log',
                'success' => 'Log został pomyślnie wyczyszczony',
                'error' => 'Błąd podczas czyszczenia logu',
                'bulk' => [
                    'success' => 'Logi zostały pomyślnie wyczyszczone',
                    'label' => 'Wyczyść zaznaczone logi',
                ],
            ],
            'close' => [
                'label' => 'Powrót',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Szukaj w logach...',
            'go_to_top' => 'Idź do góry',
            'go_to_bottom' => 'Idź na dół',
            'top' => 'Góra',
            'bottom' => 'Dół',
            'no_matching_entries' => 'Brak pasujących wpisów w logu.',
            'no_entries' => 'Brak wpisów w logu.',
            'context' => 'Kontekst',
            'stack_trace' => 'Ślad stosu',
        ],
        'detail' => [
            'title' => 'Szczegóły',
            'file_path' => 'Ścieżka pliku',
            'log_entries' => 'Wpisy',
            'size' => 'Rozmiar',
            'created_at' => 'Utworzono',
            'updated_at' => 'Zaktualizowano',
        ],
    ],
    'levels' => [
        'all' => 'Wszystkie',
        'emergency' => 'Krytyczny (Emergency)',
        'alert' => 'Alert',
        'critical' => 'Bardzo poważny (Critical)',
        'error' => 'Błąd',
        'warning' => 'Ostrzeżenie',
        'notice' => 'Informacja (Notice)',
        'info' => 'Informacja',
        'debug' => 'Debugowanie',
    ],
];
