<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Loggvisare',
    ],
    'show' => [
        'title' => 'Visa logg :log',
    ],
    'navigation' => [
        'group' => 'Loggar',
        'label' => 'Loggvisare',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Datum',
            ],
            'level' => [
                'label' => 'Nivå',
            ],
            'message' => [
                'label' => 'Meddelande',
            ],
            'filename' => [
                'label' => 'Filnamn',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Visa',
            ],
            'download' => [
                'label' => 'Hämta logg :log',
                'bulk' => [
                    'label' => 'Hämta loggar',
                    'error' => 'Fel vid hämtning av loggar',
                ],
            ],
            'delete' => [
                'label' => 'Ta bort logg :log',
                'success' => 'Logg borttagen',
                'error' => 'Fel vid borttagning av logg',
                'bulk' => [
                    'label' => 'Ta bort valda loggar',
                ],
            ],
            'clear' => [
                'label' => 'Rensa logg :log',
                'success' => 'Logg rensad',
                'error' => 'Fel vid rensning av logg',
                'bulk' => [
                    'success' => 'Loggar rensade',
                    'label' => 'Rensa valda loggar',
                ],
            ],
            'close' => [
                'label' => 'Tillbaka',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Sök i loggar...',
            'go_to_top' => 'Gå till toppen',
            'go_to_bottom' => 'Gå till botten',
            'top' => 'Toppen',
            'bottom' => 'Botten',
            'no_matching_entries' => 'Ingen matchande loggposter.',
            'no_entries' => 'Inga loggposter.',
            'context' => 'Kontext',
            'stack_trace' => 'Stacktrace',
        ],
        'detail' => [
            'title' => 'Detaljer',
            'file_path' => 'Filsökväg',
            'log_entries' => 'Poster',
            'size' => 'Storlek',
            'created_at' => 'Skapad',
            'updated_at' => 'Uppdaterad',
        ],
    ],
    'levels' => [
        'all' => 'Alla',
        'emergency' => 'Nödsituation',
        'alert' => 'Varning',
        'critical' => 'Kritisk',
        'error' => 'Fel',
        'warning' => 'Varning',
        'notice' => 'Meddelande',
        'info' => 'Info',
        'debug' => 'Felsökning',
    ],
];
