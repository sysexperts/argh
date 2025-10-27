<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require __DIR__ . '/../vendor/autoload.php';

use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;
use SysExperts\BusinessManager\Calendar\CalendarController;

$config = require __DIR__ . '/../config/database.php';
$db = new Database($config);
$sessionService = new SessionService();

// Simuliere POST-Request
$_SERVER['REQUEST_METHOD'] = 'POST';

// Simuliere eingeloggten User
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@sys-experts.de';
$_SESSION['user_name'] = 'Admin User';
$_SESSION['user_role'] = 'admin';
$_SESSION['authenticated'] = true;

// Test-Daten
$testData = [
    'title' => 'Test Event',
    'description' => 'Test Beschreibung',
    'location' => 'Büro',
    'start' => '2025-01-15T10:00',
    'end' => '2025-01-15T11:00',
    'category' => 'meeting',
    'color' => '#14b8a6'
];

echo "<h1>Calendar API Test</h1>";
echo "<h2>Test-Daten:</h2>";
echo "<pre>";
print_r($testData);
echo "</pre>";

try {
    // Erstelle PSR-7 Request
    $request = \Slim\Psr7\Factory\ServerRequestFactory::createFromGlobals();
    $request = $request->withParsedBody($testData);
    
    $response = new \Slim\Psr7\Response();
    
    $controller = new CalendarController($db, $sessionService);
    $response = $controller->store($request, $response);
    
    echo "<h2>Response Status: " . $response->getStatusCode() . "</h2>";
    echo "<h2>Response Body:</h2>";
    echo "<pre>";
    echo $response->getBody();
    echo "</pre>";
    
} catch (\Exception $e) {
    echo "<h2 style='color:red'>Exception:</h2>";
    echo "<pre>";
    echo $e->getMessage() . "\n\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}
