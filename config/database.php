<?php

declare(strict_types=1);

$env = getenv('APP_ENV') ?: 'local';
$profile = $env === 'production' ? 'production' : 'test';

return [
    'environment' => $profile,
    'charset' => 'utf8mb4',
    'connections' => [
        'test' => [
            'host' => '127.0.0.1',
            'port' => 3306,
            'name' => 'saas_agendamentos',
            'user' => 'root',
            'pass' => '',
        ],
        'production' => [
            'host' => '127.0.0.1',
            'port' => 3306,
            'name' => 'saas_agendamentos',
            'user' => 'root',
            'pass' => '',
        ],
    ],
];
