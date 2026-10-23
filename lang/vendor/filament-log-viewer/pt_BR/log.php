<?php

declare(strict_types=1);

return [
    'dashboard' => [
        'title' => 'Visualizador de Logs',
    ],
    'show' => [
        'title' => 'Visualizar log :log',
    ],
    'navigation' => [
        'group' => 'Logs',
        'label' => 'Visualizador de Logs',
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
                'label' => 'Visualizar',
            ],
            'download' => [
                'label' => 'Baixar log :log',
                'bulk' => [
                    'label' => 'Baixar logs',
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
        'modal' => [
            'search_placeholder' => 'Procurar nos logs...',
            'go_to_top' => 'Ir ao topo',
            'go_to_bottom' => 'Ir ao final',
            'top' => 'Topo',
            'bottom' => 'Final',
            'no_matching_entries' => 'Nenhuma entrada de log correspondente.',
            'no_entries' => 'Nenhuma entrada de log.',
            'context' => 'Contexto',
            'stack_trace' => 'Rastreamento de pilha',
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
