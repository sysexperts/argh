<?php
/**
 * Phinx Configuration
 */

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Lade Umgebungsvariablen
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Lade DB-Config
$dbConfig = require __DIR__ . '/config/database.php';
$driver = $dbConfig['default'];
$config = $dbConfig['connections'][$driver];

// Phinx-Konfiguration
return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinx_migrations',
        'default_environment' => 'development',
        'development' => [
            'adapter' => $driver === 'sqlite' ? 'sqlite' : $driver,
            'host' => $config['host'] ?? null,
            'name' => $driver === 'sqlite' ? $config['database'] : $config['database'],
            'user' => $config['username'] ?? null,
            'pass' => $config['password'] ?? null,
            'port' => $config['port'] ?? null,
            'charset' => $config['charset'] ?? 'utf8mb4',
        ],
    ],
    'version_order' => 'creation'
];
