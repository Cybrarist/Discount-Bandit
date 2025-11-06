<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Visor de logs',
    ],
    'show' => [
        'title' => 'Ver el log :log',
    ],
    'navigation' => [
        'group' => 'Logs',
        'label' => 'Visor de logs',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Fecha',
            ],
            'level' => [
                'label' => 'Nivel',
            ],
            'message' => [
                'label' => 'Mensaje',
            ],
            'filename' => [
                'label' => 'Nombre del archivo',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Ver',
            ],
            'download' => [
                'label' => 'Descargar el log :log',
                'bulk' => [
                    'label' => 'Descargar seleccionados',
                    'error' => 'Error al descargar los logs',
                ],
            ],
            'delete' => [
                'label' => 'Eliminar el log :log',
                'success' => 'Log eliminado con éxito',
                'error' => 'Error al eliminar el log',
                'bulk' => [
                    'label' => 'Eliminar logs seleccionados',
                ],
            ],
            'clear' => [
                'label' => 'Limpiar el log :log',
                'success' => 'Log limpiado con éxito',
                'error' => 'Error limpiando el log',
                'bulk' => [
                    'success' => 'Logs limpiados con éxito',
                    'label' => 'Limpiar logs seleccionados',
                ],
            ],
            'close' => [
                'label' => 'Volver',
            ],
        ],
        'detail' => [
            'title' => 'Detalle',
            'file_path' => 'Ruta del archivo',
            'log_entries' => 'Entradas',
            'size' => 'Tamaño',
            'created_at' => 'Creado el',
            'updated_at' => 'Actualizado el',
        ],
    ],
    'levels' => [
        'all' => 'Todos',
        'emergency' => 'Emergencia',
        'alert' => 'Alerta',
        'critical' => 'Crítico',
        'error' => 'Error',
        'warning' => 'Advertencia',
        'notice' => 'Aviso',
        'info' => 'Información',
        'debug' => 'Depuración',
    ],
];
