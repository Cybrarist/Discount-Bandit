<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Log-Viewer',
    ],
    'show' => [
        'title' => 'Log :log anzeigen',
    ],
    'navigation' => [
        'group' => 'Logs',
        'label' => 'Log-Viewer',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Datum',
            ],
            'level' => [
                'label' => 'Stufe',
            ],
            'message' => [
                'label' => 'Nachricht',
            ],
            'filename' => [
                'label' => 'Dateiname',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Ansehen',
            ],
            'download' => [
                'label' => 'Log :log herunterladen',
                'bulk' => [
                    'label' => 'Ausgewählte herunterladen',
                    'error' => 'Fehler beim Herunterladen der Logs',
                ],
            ],
            'delete' => [
                'label' => 'Log :log löschen',
                'success' => 'Log erfolgreich gelöscht',
                'error' => 'Fehler beim Löschen des Logs',
                'bulk' => [
                    'label' => 'Ausgewählte Logs löschen',
                ],
            ],
            'clear' => [
                'label' => 'Protokoll löschen :log',
                'success' => 'Protokoll erfolgreich gelöscht',
                'error' => 'Fehler beim Löschen des Protokolls',
                'bulk' => [
                    'success' => 'Protokolle erfolgreich gelöscht',
                    'label' => 'Ausgewählte Protokolle löschen',
                ],
            ],
            'close' => [
                'label' => 'Zurück',
            ],
        ],
        'detail' => [
            'title' => 'Detail',
            'file_path' => 'Dateipfad',
            'log_entries' => 'Einträge',
            'size' => 'Größe',
            'created_at' => 'Erstellt am',
            'updated_at' => 'Aktualisiert am',
        ],
    ],
    'levels' => [
        'all' => 'Alle',
        'emergency' => 'Notfall',
        'alert' => 'Alarm',
        'critical' => 'Kritisch',
        'error' => 'Fehler',
        'warning' => 'Warnung',
        'notice' => 'Hinweis',
        'info' => 'Info',
        'debug' => 'Debug',
    ],
];
