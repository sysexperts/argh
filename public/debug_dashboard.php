<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\AuthService;
use SysExperts\BusinessManager\Auth\SessionManager;

// Lade Umgebungsvariablen
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Starte Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lade Konfiguration
$config = [
    'database' => require __DIR__ . '/../config/database.php',
];

try {
    $db = new Database($config['database']);
    $pdo = $db->getConnection();
    $authService = new AuthService($pdo);
    $sessionManager = new SessionManager($pdo);
    
    echo "✅ Database verbunden\n";
    echo "✅ Session ID: " . ($sessionManager->getCurrentSessionId() ?? 'keine') . "\n";
    
    $currentUser = $sessionManager->getCurrentUser($authService);
    
    if (!$currentUser) {
        echo "❌ Kein User eingeloggt\n";
        exit;
    }
    
    echo "✅ User: " . $currentUser->getEmail() . "\n";
    echo "✅ Tenant ID: " . $currentUser->getTenantId() . "\n";
    
    // Teste DB-Queries
    $tenantId = $currentUser->getTenantId();
    
    echo "\n--- Teste Queries ---\n";
    
    $result = $db->fetchOne('SELECT COUNT(*) as count FROM users WHERE tenant_id = ? AND is_active = 1', [$tenantId]);
    echo "✅ Users: " . ($result['count'] ?? 0) . "\n";
    
} catch (Exception $e) {
    echo "❌ Fehler: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}
