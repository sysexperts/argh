<?php
/**
 * Test: Prüfe ob Helpdesk-Route registriert ist
 */

require __DIR__ . '/vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();

// Registriere Services
$container->set(\SysExperts\BusinessManager\Database\Database::class, function() {
    $config = require __DIR__ . '/config/database.php';
    return new \SysExperts\BusinessManager\Database\Database($config);
});

$container->set(\SysExperts\BusinessManager\Auth\SessionService::class, function() {
    return new \SysExperts\BusinessManager\Auth\SessionService();
});

AppFactory::setContainer($container);
$app = AppFactory::create();

// Lade Routes
require __DIR__ . '/routes/web.php';

// Hole alle Routes
$routeCollector = $app->getRouteCollector();
$routes = $routeCollector->getRoutes();

echo "=== Registrierte Routes ===\n\n";

$calendarRoutes = [];
foreach ($routes as $route) {
    $pattern = $route->getPattern();
    if (strpos($pattern, 'calendar') !== false) {
        $calendarRoutes[] = [
            'pattern' => $pattern,
            'methods' => implode(', ', $route->getMethods())
        ];
    }
}

if (empty($calendarRoutes)) {
    echo "❌ KEINE Calendar-Routes gefunden!\n";
} else {
    echo "✓ Calendar-Routes gefunden:\n\n";
    foreach ($calendarRoutes as $route) {
        echo "  {$route['methods']} {$route['pattern']}\n";
    }
}
