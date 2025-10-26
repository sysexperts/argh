<?php
/**
 * Debug: Prüfe Tenant-IDs der User
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

    echo "=== User Tenant-IDs ===\n\n";
    
    $users = $pdo->query("SELECT id, email, tenant_id, role FROM users")->fetchAll();
    
    foreach ($users as $user) {
        echo "ID: {$user['id']} | Email: {$user['email']} | Tenant: {$user['tenant_id']} | Role: {$user['role']}\n";
    }
    
    echo "\n=== Rechnungen pro Tenant ===\n\n";
    
    $invoices = $pdo->query("SELECT tenant_id, COUNT(*) as count, SUM(total) as total FROM bm_invoices GROUP BY tenant_id")->fetchAll();
    
    foreach ($invoices as $inv) {
        echo "Tenant {$inv['tenant_id']}: {$inv['count']} Rechnungen, Total: €{$inv['total']}\n";
    }
    
} catch (PDOException $e) {
    die("Fehler: " . $e->getMessage() . "\n");
}
