<?php
/**
 * Test Login
 */

require __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config/database.php';
$dbConfig = $config['connections'][$config['default']];

try {
    $dbPath = $dbConfig['database'];
    $pdo = new PDO("sqlite:$dbPath", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "=== Test Login ===\n\n";
    
    // Test Admin
    $email = 'admin@sys-experts.de';
    $password = 'admin123';
    
    $user = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $user->execute([$email]);
    $userData = $user->fetch();
    
    if ($userData) {
        echo "✓ User gefunden: {$userData['email']}\n";
        echo "  ID: {$userData['id']}\n";
        echo "  Tenant: {$userData['tenant_id']}\n";
        echo "  Role: {$userData['role']}\n";
        echo "  Password Hash: " . substr($userData['password_hash'], 0, 20) . "...\n";
        
        if (password_verify($password, $userData['password_hash'])) {
            echo "  ✓ Passwort korrekt!\n";
        } else {
            echo "  ✗ Passwort FALSCH!\n";
        }
    } else {
        echo "✗ User nicht gefunden!\n";
    }
    
    echo "\n";
    
    // Test Normal User
    $email = 'user@sys-experts.de';
    $password = 'user123';
    
    $user = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $user->execute([$email]);
    $userData = $user->fetch();
    
    if ($userData) {
        echo "✓ User gefunden: {$userData['email']}\n";
        echo "  ID: {$userData['id']}\n";
        echo "  Tenant: {$userData['tenant_id']}\n";
        echo "  Role: {$userData['role']}\n";
        echo "  Password Hash: " . substr($userData['password_hash'], 0, 20) . "...\n";
        
        if (password_verify($password, $userData['password_hash'])) {
            echo "  ✓ Passwort korrekt!\n";
        } else {
            echo "  ✗ Passwort FALSCH!\n";
        }
    } else {
        echo "✗ User nicht gefunden!\n";
    }
    
} catch (PDOException $e) {
    die("Fehler: " . $e->getMessage() . "\n");
}
