<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');

echo "=== Admin User Check ===\n\n";

$stmt = $pdo->query("SELECT id, email, password_hash, tenant_id, role, is_active FROM users WHERE email = 'admin@sys-experts.de'");
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "✓ Admin gefunden:\n";
    echo "  ID: {$admin['id']}\n";
    echo "  Email: {$admin['email']}\n";
    echo "  Tenant: {$admin['tenant_id']}\n";
    echo "  Role: {$admin['role']}\n";
    echo "  Active: {$admin['is_active']}\n";
    echo "  Hash: " . substr($admin['password_hash'], 0, 30) . "...\n\n";
    
    // Test Passwort
    $password = 'admin123';
    if (password_verify($password, $admin['password_hash'])) {
        echo "✓ Passwort 'admin123' ist KORREKT\n";
    } else {
        echo "✗ Passwort 'admin123' ist FALSCH\n";
        echo "\nVersuche neuen Hash zu erstellen...\n";
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        echo "Neuer Hash: $newHash\n";
        
        // Update
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = 'admin@sys-experts.de'");
        $stmt->execute([$newHash]);
        echo "✓ Passwort wurde zurückgesetzt!\n";
    }
} else {
    echo "✗ Admin nicht gefunden!\n";
}
