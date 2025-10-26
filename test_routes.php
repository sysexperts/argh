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

$helpdeskRoutes = [];
foreach ($routes as $route) {
    $pattern = $route->getPattern();
    if (strpos($pattern, 'helpdesk') !== false) {
        $helpdeskRoutes[] = [
            'pattern' => $pattern,
            'methods' => implode(', ', $route->getMethods())
        ];
    }
}

if (empty($helpdeskRoutes)) {
    echo "❌ KEINE Helpdesk-Routes gefunden!\n";
} else {
    echo "✓ Helpdesk-Routes gefunden:\n\n";
    foreach ($helpdeskRoutes as $route) {
        echo "  {$route['methods']} {$route['pattern']}\n";
    }
}
