<?php
/**
 * Phinx Configuration
 */

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Lade Umgebungsvariablen
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

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
            'adapter' => 'sqlite',
            'name' => __DIR__ . '/database/business_manager.sqlite',
        ],
    ],
    'version_order' => 'creation'
];
