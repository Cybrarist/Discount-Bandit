<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Loggviser',
    ],
    'show' => [
        'title' => 'Se logg :log',
    ],
    'navigation' => [
        'group' => 'Logger',
        'label' => 'Loggviser',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Dato',
            ],
            'level' => [
                'label' => 'Nivå',
            ],
            'message' => [
                'label' => 'Melding',
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
                'label' => 'Last ned logg :log',
                'bulk' => [
                    'label' => 'Last ned logger',
                    'error' => 'Feil ved nedlasting av logger',
                ],
            ],
            'delete' => [
                'label' => 'Slett logg :log',
                'success' => 'Logg ble slettet',
                'error' => 'Feil ved sletting av logg',
                'bulk' => [
                    'label' => 'Slett valgte logger',
                ],
            ],
            'clear' => [
                'label' => 'Tøm logg :log',
                'success' => 'Logg ble tømt',
                'error' => 'Feil ved tømming av logg',
                'bulk' => [
                    'success' => 'Logger ble tømt',
                    'label' => 'Tøm valgte logger',
                ],
            ],
            'close' => [
                'label' => 'Tilbake',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Søk i logger...',
            'go_to_top' => 'Gå til toppen',
            'go_to_bottom' => 'Gå til bunnen',
            'top' => 'Toppen',
            'bottom' => 'Bunnen',
            'no_matching_entries' => 'Ingen samsvarende loggoppføringer.',
            'no_entries' => 'Ingen loggoppføringer.',
            'context' => 'Kontekst',
            'stack_trace' => 'Stabelsporing',
        ],
        'detail' => [
            'title' => 'Detaljer',
            'file_path' => 'Filsti',
            'log_entries' => 'Oppføringer',
            'size' => 'Størrelse',
            'created_at' => 'Opprettet',
            'updated_at' => 'Oppdatert',
        ],
    ],
    'levels' => [
        'all' => 'Alle',
        'emergency' => 'Nødsituasjon',
        'alert' => 'Advarsel',
        'critical' => 'Kritisk',
        'error' => 'Feil',
        'warning' => 'Advarsel',
        'notice' => 'Merknad',
        'info' => 'Info',
        'debug' => 'Debug',
    ],
];
