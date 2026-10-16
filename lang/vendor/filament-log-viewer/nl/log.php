<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Logweergave',
    ],
    'show' => [
        'title' => 'Log weergeven :log',
    ],
    'navigation' => [
        'group' => 'Logs',
        'label' => 'Logweergave',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Datum',
            ],
            'level' => [
                'label' => 'Niveau',
            ],
            'message' => [
                'label' => 'Bericht',
            ],
            'filename' => [
                'label' => 'Bestandsnaam',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Weergeven',
            ],
            'download' => [
                'label' => 'Log downloaden :log',
                'bulk' => [
                    'label' => 'Logs downloaden',
                    'error' => 'Fout bij downloaden van logs',
                ],
            ],
            'delete' => [
                'label' => 'Log verwijderen :log',
                'success' => 'Log succesvol verwijderd',
                'error' => 'Fout bij verwijderen van log',
                'bulk' => [
                    'label' => 'Geselecteerde logs verwijderen',
                ],
            ],
            'clear' => [
                'label' => 'Log wissen :log',
                'success' => 'Log succesvol gewist',
                'error' => 'Fout bij wissen van log',
                'bulk' => [
                    'success' => 'Logs succesvol gewist',
                    'label' => 'Geselecteerde logs wissen',
                ],
            ],
            'close' => [
                'label' => 'Terug',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Logs doorzoeken...',
            'go_to_top' => 'Naar boven',
            'go_to_bottom' => 'Naar beneden',
            'top' => 'Boven',
            'bottom' => 'Beneden',
            'no_matching_entries' => 'Geen overeenkomende logitems.',
            'no_entries' => 'Geen logitems.',
            'context' => 'Context',
            'stack_trace' => 'Stack-tracering',
        ],
        'detail' => [
            'title' => 'Details',
            'file_path' => 'Bestandspad',
            'log_entries' => 'Vermeldingen',
            'size' => 'Grootte',
            'created_at' => 'Gemaakt',
            'updated_at' => 'Bijgewerkt',
        ],
    ],
    'levels' => [
        'all' => 'Alles',
        'emergency' => 'Noodsituatie',
        'alert' => 'Waarschuwing',
        'critical' => 'Kritiek',
        'error' => 'Fout',
        'warning' => 'Waarschuwing',
        'notice' => 'Opmerking',
        'info' => 'Informatie',
        'debug' => 'Debug',
    ],
];
