<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Visualizador de logs',
    ],
    'show' => [
        'title' => 'Ver log :log',
    ],
    'navigation' => [
        'group' => 'Logs',
        'label' => 'Visualizador de logs',
        'sort' => 100,
    ],
    'table' => [
        'columns' => [
            'date' => [
                'label' => 'Data',
            ],
            'level' => [
                'label' => 'Nível',
            ],
            'message' => [
                'label' => 'Mensagem',
            ],
            'filename' => [
                'label' => 'Nome do arquivo',
            ],
        ],
        'actions' => [
            'view' => [
                'label' => 'Ver',
            ],
            'download' => [
                'label' => 'Baixar log :log',
                'bulk' => [
                    'label' => 'Baixar selecionados',
                    'error' => 'Erro ao baixar os logs',
                ],
            ],
            'delete' => [
                'label' => 'Excluir log :log',
                'success' => 'Log excluído com sucesso',
                'error' => 'Erro ao excluir o log',
                'bulk' => [
                    'label' => 'Excluir logs selecionados',
                ],
            ],
            'clear' => [
                'label' => 'Limpar log :log',
                'success' => 'Log limpo com sucesso',
                'error' => 'Erro ao limpar o log',
                'bulk' => [
                    'success' => 'Logs limpos com sucesso',
                    'label' => 'Limpar logs selecionados',
                ],
            ],
            'close' => [
                'label' => 'Voltar',
            ],
        ],
        'detail' => [
            'title' => 'Detalhes',
            'file_path' => 'Caminho do arquivo',
            'log_entries' => 'Entradas',
            'size' => 'Tamanho',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ],
    ],
    'levels' => [
        'all' => 'Todos',
        'emergency' => 'Emergência',
        'alert' => 'Alerta',
        'critical' => 'Crítico',
        'error' => 'Erro',
        'warning' => 'Aviso',
        'notice' => 'Aviso',
        'info' => 'Informação',
        'debug' => 'Depuração',
    ],
];
