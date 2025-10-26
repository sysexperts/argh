<?php
/**
 * Datenbank-Konfiguration
 */

return [
    'default' => $_ENV['DB_CONNECTION'] ?? 'sqlite',
    
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../' . ($_ENV['DB_DATABASE'] ?? 'database/business_manager.sqlite'),
        ],
        
        'mysql' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'database' => $_ENV['DB_DATABASE'] ?? 'business_manager',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['DB_PORT'] ?? 5432,
            'database' => $_ENV['DB_DATABASE'] ?? 'business_manager',
            'username' => $_ENV['DB_USERNAME'] ?? 'postgres',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8',
        ],
    ],
];
