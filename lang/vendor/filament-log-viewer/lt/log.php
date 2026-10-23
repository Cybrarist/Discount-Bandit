<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Žurnalo žiūryklė',
    ],
    'show' => [
        'title' => 'Žiūrėti žurnalą :log',
    ],
    'navigation' => [
        'group' => 'Žurnalai',
        'label' => 'Žurnalo žiūryklė',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Data',
            ],
            'level' => [
                'label' => 'Lygis',
            ],
            'message' => [
                'label' => 'Pranešimas',
            ],
            'filename' => [
                'label' => 'Failas',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Žiūrėti',
            ],
            'download' => [
                'label' => 'Atsisiųsti žurnalą :log',
                'bulk' => [
                    'label' => 'Atsisiųsti žurnalus',
                    'error' => 'Klaida atsisiunčiant žurnalus',
                ],
            ],
            'delete' => [
                'label' => 'Ištrinti žurnalą :log',
                'success' => 'Žurnalas sėkmingai ištrintas',
                'error' => 'Klaida ištrinant žurnalą',
                'bulk' => [
                    'label' => 'Ištrinti pasirinktus žurnalus',
                ],
            ],
            'clear' => [
                'label' => 'Išvalyti žurnalą :log',
                'success' => 'Žurnalas sėkmingai išvalytas',
                'error' => 'Klaida išvalant žurnalą',
                'bulk' => [
                    'success' => 'Žurnalai sėkmingai išvalyti',
                    'label' => 'Išvalyti pasirinktus žurnalus',
                ],
            ],
            'close' => [
                'label' => 'Grįžti',
            ],
        ],
        'modal' => [
            'search_placeholder' => 'Ieškoti žurnalų...',
            'go_to_top' => 'Į viršų',
            'go_to_bottom' => 'Į apačią',
            'top' => 'Viršus',
            'bottom' => 'Apačia',
            'no_matching_entries' => 'Nėra atitinkančių žurnalo įrašų.',
            'no_entries' => 'Nėra žurnalo įrašų.',
            'context' => 'Kontekstas',
            'stack_trace' => 'Steko pėdsakas',
        ],
        'detail' => [
            'title' => 'Detalės',
            'file_path' => 'Failo kelias',
            'log_entries' => 'Įrašai',
            'size' => 'Dydis',
            'created_at' => 'Sukurta',
            'updated_at' => 'Atnaujinta',
        ],
    ],
    'levels' => [
        'all' => 'Visi',
        'emergency' => 'Nepaprastas',
        'alert' => 'Perspėjimas',
        'critical' => 'Kritinis',
        'error' => 'Klaida',
        'warning' => 'Perspėjimas',
        'notice' => 'Pranešimas',
        'info' => 'Informacija',
        'debug' => 'Derinimas',
    ],
];
