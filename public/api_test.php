<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<h1>API Test</h1>";

// Simuliere POST zu /api/calendar/events
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/calendar/events';

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['authenticated'] = true;

try {
    require __DIR__ . '/../vendor/autoload.php';
    
    echo "<p>✓ Autoload OK</p>";
    
    // Lade Slim App
    $container = new \DI\Container();
    
    $container->set(\SysExperts\BusinessManager\Database\Database::class, function() {
        $config = require __DIR__ . '/../config/database.php';
        return new \SysExperts\BusinessManager\Database\Database($config);
    });
    
    $container->set(\SysExperts\BusinessManager\Auth\SessionService::class, function() {
        return new \SysExperts\BusinessManager\Auth\SessionService();
    });
    
    echo "<p>✓ Container OK</p>";
    
    \Slim\Factory\AppFactory::setContainer($container);
    $app = \Slim\Factory\AppFactory::create();
    
    echo "<p>✓ App OK</p>";
    
    // Lade Routes
    require __DIR__ . '/../routes/web.php';
    
    echo "<p>✓ Routes OK</p>";
    
    // Teste Calendar Controller direkt
    $db = $container->get(\SysExperts\BusinessManager\Database\Database::class);
    $session = $container->get(\SysExperts\BusinessManager\Auth\SessionService::class);
    
    $controller = new \SysExperts\BusinessManager\Calendar\CalendarController($db, $session);
    
    echo "<p>✓ Controller erstellt</p>";
    
    // Erstelle Request
    $request = \Slim\Psr7\Factory\ServerRequestFactory::createFromGlobals();
    $request = $request->withParsedBody([
        'title' => 'Test',
        'start' => '2025-01-15T10:00',
        'end' => '2025-01-15T11:00'
    ]);
    
    $response = new \Slim\Psr7\Response();
    
    echo "<p>Rufe store() auf...</p>";
    
    $response = $controller->store($request, $response);
    
    echo "<p>✓ Store erfolgreich!</p>";
    echo "<p>Status: " . $response->getStatusCode() . "</p>";
    echo "<p>Body: " . $response->getBody() . "</p>";
    
} catch (\Throwable $e) {
    echo "<h2 style='color:red'>FEHLER:</h2>";
    echo "<pre>";
    echo "Message: " . $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}
