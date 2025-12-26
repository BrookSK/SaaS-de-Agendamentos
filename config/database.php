<?php

declare(strict_types=1);

return [
    // Defina manualmente qual conexão o sistema deve usar:
    // 'test' ou 'production'
    'environment' => 'test',
    'charset' => 'utf8mb4',
    'connections' => [
        'test' => [
            'host' => 'localhost',
            'port' => 3306,
            'name' => 'saas_agendamentos_teste',
            'user' => 'saas_agendamentos_teste',
            'pass' => '^5N2jro6XdN~dtar',
        ],
        'production' => [
            'host' => 'localhost',
            'port' => 3306,
            'name' => 'saas_agendamentos',
            'user' => 'saas_agendamentos',
            'pass' => '^5N2jro6XdN~dtar',
        ],
    ],
];
