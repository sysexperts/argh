<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\AuthService;

// Lade Umgebungsvariablen
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Lade Konfiguration
$config = [
    'database' => require __DIR__ . '/config/database.php',
];

try {
    $db = new Database($config['database']);
    $pdo = $db->getConnection();
    $authService = new AuthService($pdo);
    
    echo "=== Teste Login ===\n\n";
    
    // Hole alle User
    $users = $pdo->query("SELECT id, email, first_name, last_name FROM users LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Vorhandene User:\n";
    foreach ($users as $user) {
        echo "- ID: {$user['id']}, Email: {$user['email']}, Name: {$user['first_name']} {$user['last_name']}\n";
    }
    
    if (count($users) === 0) {
        echo "\n❌ Keine User in der Datenbank!\n";
        echo "Bitte erstelle zuerst einen Test-User.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Fehler: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}
