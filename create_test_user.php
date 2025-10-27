<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Lade Umgebungsvariablen
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$pdo = new PDO('sqlite:database/business_manager.sqlite');

// Erstelle Test-User mit bekanntem Passwort
$email = 'test@example.com';
$password = 'password123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prüfe ob User existiert
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo "❌ User existiert bereits: $email\n";
    exit;
}

// Hole ersten Tenant
$tenant = $pdo->query("SELECT id FROM tenants LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$tenant) {
    // Erstelle Tenant
    $pdo->exec("INSERT INTO tenants (name, domain, created_at, updated_at) VALUES ('Test Company', 'test.local', datetime('now'), datetime('now'))");
    $tenantId = $pdo->lastInsertId();
} else {
    $tenantId = $tenant['id'];
}

// Erstelle User
$stmt = $pdo->prepare("
    INSERT INTO users (
        tenant_id, email, password_hash, first_name, last_name,
        role, is_active, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, 'admin', 1, datetime('now'), datetime('now'))
");

$stmt->execute([
    $tenantId,
    $email,
    $hashedPassword,
    'Test',
    'User'
]);

echo "✅ Test-User erstellt!\n";
echo "Email: $email\n";
echo "Passwort: $password\n";
echo "Tenant ID: $tenantId\n";
