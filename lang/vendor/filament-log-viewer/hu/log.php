<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Napló megjelenítő',
    ],
    'show' => [
        'title' => 'Naplóbejegyzés megtekintése :log',
    ],
    'navigation' => [
        'group' => 'Naplók',
        'label' => 'Napló megjelenítő',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Dátum',
            ],
            'level' => [
                'label' => 'Szint',
            ],
            'message' => [
                'label' => 'Üzenet',
            ],
            'filename' => [
                'label' => 'Fájl neve',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Megtekintés',
            ],
            'download' => [
                'label' => 'Naplóbejegyzés letöltése :log',
                'bulk' => [
                    'label' => 'Naplók letöltése',
                    'error' => 'Hiba a naplók letöltésekor',
                ],
            ],
            'delete' => [
                'label' => 'Naplóbejegyzés törlése :log',
                'success' => 'Naplóbejegyzés sikeresen törölve',
                'error' => 'Hiba a naplóbejegyzés törléskor',
                'bulk' => [
                    'label' => 'Kiválasztott naplók törlése',
                ],
            ],
            'clear' => [
                'label' => 'Naplóbejegyzés törlése :log',
                'success' => 'Naplóbejegyzés sikeresen törölve',
                'error' => 'Hiba a naplóbejegyzés törléskor',
                'bulk' => [
                    'success' => 'Naplóbejegyzések sikeresen törölve',
                    'label' => 'Kiválasztott naplók törlése',
                ],
            ],
            'close' => [
                'label' => 'Vissza',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Naplók keresése...',
            'go_to_top' => 'Ugrás az elejére',
            'go_to_bottom' => 'Ugrás a végére',
            'top' => 'Eleje',
            'bottom' => 'Vége',
            'no_matching_entries' => 'Nem találhatók megfelelő naplóbejegyzések.',
            'no_entries' => 'Nincsenek naplóbejegyzések.',
            'context' => 'Kontextus',
            'stack_trace' => 'Veremnyom',
        ],
        'detail' => [
            'title' => 'Részletek',
            'file_path' => 'Fájl elérési útja',
            'log_entries' => 'Bejegyzések',
            'size' => 'Méret',
            'created_at' => 'Létrehozva',
            'updated_at' => 'Frissítve',
        ],
    ],
    'levels' => [
        'all' => 'Minden',
        'emergency' => 'Sürgősség',
        'alert' => 'Figyelmeztetés',
        'critical' => 'Kritikus',
        'error' => 'Hiba',
        'warning' => 'Figyelmeztetés',
        'notice' => 'Megjegyzés',
        'info' => 'Információ',
        'debug' => 'Hibakeresés',
    ],
];
