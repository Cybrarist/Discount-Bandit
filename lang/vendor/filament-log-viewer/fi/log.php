<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Lokinäkymä',
    ],
    'show' => [
        'title' => 'Näytä loki :log',
    ],
    'navigation' => [
        'group' => 'Lokit',
        'label' => 'Lokinäkymä',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Päivämäärä',
            ],
            'level' => [
                'label' => 'Taso',
            ],
            'message' => [
                'label' => 'Viesti',
            ],
            'filename' => [
                'label' => 'Tiedoston nimi',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Näytä',
            ],
            'download' => [
                'label' => 'Lataa loki :log',
                'bulk' => [
                    'label' => 'Lataa lokit',
                    'error' => 'Virhe lokien lataamisessa',
                ],
            ],
            'delete' => [
                'label' => 'Poista loki :log',
                'success' => 'Loki poistettiin onnistuneesti',
                'error' => 'Virhe lokin poistamisessa',
                'bulk' => [
                    'label' => 'Poista valitut lokit',
                ],
            ],
            'clear' => [
                'label' => 'Tyhjennä loki :log',
                'success' => 'Loki tyhjennetty onnistuneesti',
                'error' => 'Virhe lokin tyhjentämisessä',
                'bulk' => [
                    'success' => 'Lokit tyhjennetty onnistuneesti',
                    'label' => 'Tyhjennä valitut lokit',
                ],
            ],
            'close' => [
                'label' => 'Takaisin',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Hae lokeista...',
            'go_to_top' => 'Siirry ylös',
            'go_to_bottom' => 'Siirry alas',
            'top' => 'Ylös',
            'bottom' => 'Alas',
            'no_matching_entries' => 'Ei vastaavia lokitapahtumia.',
            'no_entries' => 'Ei lokitapahtumia.',
            'context' => 'Konteksti',
            'stack_trace' => 'Pinojäljitys',
        ],
        'detail' => [
            'title' => 'Yksityiskohdat',
            'file_path' => 'Tiedoston polku',
            'log_entries' => 'Tapahtumat',
            'size' => 'Koko',
            'created_at' => 'Luotu',
            'updated_at' => 'Päivitetty',
        ],
    ],
    'levels' => [
        'all' => 'Kaikki',
        'emergency' => 'Hätätilanne',
        'alert' => 'Hälytys',
        'critical' => 'Kriittinen',
        'error' => 'Virhe',
        'warning' => 'Varoitus',
        'notice' => 'Huomautus',
        'info' => 'Tieto',
        'debug' => 'Virheenkorjaus',
    ],
];
