<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Visualizzatore di log',
    ],
    'show' => [
        'title' => 'Vedi il log :log',
    ],
    'navigation' => [
        'group' => 'Log',
        'label' => 'Visualizzatore di log',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Data',
            ],
            'level' => [
                'label' => 'Livello',
            ],
            'message' => [
                'label' => 'Messaggio',
            ],
            'filename' => [
                'label' => 'Nome del file',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Vedi',
            ],
            'download' => [
                'label' => 'Scarica il log :log',
                'bulk' => [
                    'label' => 'Scarica i log selezionati',
                    'error' => 'Errore durante il download dei log',
                ],
            ],
            'delete' => [
                'label' => 'Elimina il log :log',
                'success' => 'Log eliminato con successo',
                'error' => 'Errore durante l\'eliminazione del log',
                'bulk' => [
                    'label' => 'Elimina i log selezionati',
                ],
            ],
            'clear' => [
                'label' => 'Cancella registro :log',
                'success' => 'Registro cancellato con successo',
                'error' => 'Errore durante la cancellazione del registro',
                'bulk' => [
                    'success' => 'Log cancellati con successo',
                    'label' => 'Cancella i log selezionati',
                ],
            ],
            'close' => [
                'label' => 'Indietro',
            ],
        ],
        'detail' => [
            'title' => 'Dettaglio',
            'file_path' => 'Percorso del file',
            'log_entries' => 'Voci',
            'size' => 'Dimensione',
            'created_at' => 'Creato il',
            'updated_at' => 'Aggiornato il',
        ],
    ],
    'levels' => [
        'all' => 'Tutti',
        'emergency' => 'Emergenza',
        'alert' => 'Allerta',
        'critical' => 'Critico',
        'error' => 'Errore',
        'warning' => 'Avviso',
        'notice' => 'Notifica',
        'info' => 'Informazioni',
        'debug' => 'Debug',
    ],
];
