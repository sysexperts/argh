<?php
/**
 * Bootstrap Application
 * 
 * @package SysExperts\BusinessManager
 */

declare(strict_types=1);

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use DI\Container;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;

// Lade Umgebungsvariablen
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Starte Session frühzeitig, damit $_SESSION überall verfügbar ist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lade Konfiguration
$config = [
    'app' => require __DIR__ . '/../config/app.php',
    'database' => require __DIR__ . '/../config/database.php',
    'modules' => require __DIR__ . '/../config/modules.php',
    'license' => require __DIR__ . '/../config/license.php',
];

// Setze Timezone
date_default_timezone_set($config['app']['timezone']);

// Erstelle Container
$container = new Container();

// Registriere Konfiguration im Container
$container->set('config', function() use ($config) {
    return $config;
});

// Registriere Database im Container
$container->set(Database::class, function() use ($config) {
    return new Database($config['database']);
});

// Registriere SessionService im Container
$container->set(SessionService::class, function() {
    return new SessionService();
});

// Erstelle Slim App mit Container
AppFactory::setContainer($container);
$app = AppFactory::create();

// Error Handling
$app->addErrorMiddleware(
    $config['app']['debug'],
    true,
    true
);

// Lade Routes
require __DIR__ . '/../routes/web.php';

return $app;
