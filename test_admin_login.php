<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\AuthService;

$config = require __DIR__ . '/config/database.php';
$db = new Database($config);
$pdo = $db->getConnection();

echo "=== Test Admin Login Flow ===\n\n";

$email = 'admin@sys-experts.de';
$password = 'admin123';

try {
    // Schritt 1: AuthService erstellen
    $authService = new AuthService($pdo, 1); // tenant_id = 1
    echo "✓ AuthService erstellt (tenant_id = 1)\n";
    
    // Schritt 2: Login
    $user = $authService->login($email, $password);
    
    if ($user) {
        echo "✓ Login erfolgreich!\n";
        echo "  User ID: {$user->getId()}\n";
        echo "  Email: {$user->getEmail()}\n";
        echo "  Tenant: {$user->getTenantId()}\n";
        echo "  Role: {$user->getRole()}\n";
        echo "  Name: {$user->getFullName()}\n";
    } else {
        echo "✗ Login fehlgeschlagen (user = null)\n";
        
        // Debug: Manuelle Query
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            echo "\n⚠️ User existiert in DB:\n";
            print_r($userData);
            
            // Test Passwort
            if (password_verify($password, $userData['password_hash'])) {
                echo "\n✓ Passwort ist korrekt!\n";
                echo "⚠️ Problem muss im AuthService->login() sein!\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "✗ Exception: {$e->getMessage()}\n";
    echo $e->getTraceAsString() . "\n";
}
