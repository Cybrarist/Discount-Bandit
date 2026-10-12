<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Logfremviser',
    ],
    'show' => [
        'title' => 'Se log :log',
    ],
    'navigation' => [
        'group' => 'Logs',
        'label' => 'Logfremviser',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Dato',
            ],
            'level' => [
                'label' => 'Niveau',
            ],
            'message' => [
                'label' => 'Besked',
            ],
            'filename' => [
                'label' => 'Filnavn',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Se',
            ],
            'download' => [
                'label' => 'Hent log :log',
                'bulk' => [
                    'label' => 'Hent logs',
                    'error' => 'Fejl ved hentning af logs',
                ],
            ],
            'delete' => [
                'label' => 'Slet log :log',
                'success' => 'Log blev slettet med succes',
                'error' => 'Fejl ved sletning af log',
                'bulk' => [
                    'label' => 'Slet valgte logs',
                ],
            ],
            'clear' => [
                'label' => 'Ryd log :log',
                'success' => 'Log blev ryddet med succes',
                'error' => 'Fejl ved rydning af log',
                'bulk' => [
                    'success' => 'Logs blev ryddet med succes',
                    'label' => 'Ryd valgte logs',
                ],
            ],
            'close' => [
                'label' => 'Tilbage',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Søg i logs...',
            'go_to_top' => 'Gå til top',
            'go_to_bottom' => 'Gå til bund',
            'top' => 'Top',
            'bottom' => 'Bund',
            'no_matching_entries' => 'Ingen matchende logposter.',
            'no_entries' => 'Ingen logposter.',
            'context' => 'Kontekst',
            'stack_trace' => 'Stacktrace',
        ],
        'detail' => [
            'title' => 'Detaljer',
            'file_path' => 'Filsti',
            'log_entries' => 'Posteringer',
            'size' => 'Størrelse',
            'created_at' => 'Oprettet',
            'updated_at' => 'Opdateret',
        ],
    ],
    'levels' => [
        'all' => 'Alle',
        'emergency' => 'Nødvendig',
        'alert' => 'Advarsel',
        'critical' => 'Kritisk',
        'error' => 'Fejl',
        'warning' => 'Advarsel',
        'notice' => 'Notis',
        'info' => 'Info',
        'debug' => 'Debug',
    ],
];
