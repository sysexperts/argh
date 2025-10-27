<?php
/**
 * Seed Partner Console mit Test-Mandanten
 */

require __DIR__ . '/../vendor/autoload.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/business_manager.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Seed Partner Console ===\n\n";

// Test-Mandanten erstellen
$testTenants = [
    [
        'tenant_name' => 'demo',
        'company_name' => 'Demo GmbH',
        'domain' => 'demo.sys-experts.de',
        'contact_email' => 'info@demo-gmbh.de',
        'contact_phone' => '+49 123 456789',
        'tenant_status' => 'active',
        'server_host' => 'server1.sys-experts.de',
        'server_ip' => '192.168.1.100',
        'installed_version' => '1.0.0',
    ],
    [
        'tenant_name' => 'testfirma',
        'company_name' => 'Test Firma AG',
        'domain' => 'testfirma.sys-experts.de',
        'contact_email' => 'kontakt@testfirma.de',
        'contact_phone' => '+49 987 654321',
        'tenant_status' => 'trial',
        'trial_until' => date('Y-m-d', strtotime('+30 days')),
        'server_host' => 'server2.sys-experts.de',
        'server_ip' => '192.168.1.101',
        'installed_version' => '1.0.0',
    ],
    [
        'tenant_name' => 'beispiel',
        'company_name' => 'Beispiel UG',
        'domain' => 'beispiel.sys-experts.de',
        'contact_email' => 'mail@beispiel-ug.de',
        'tenant_status' => 'active',
        'server_host' => 'server1.sys-experts.de',
        'server_ip' => '192.168.1.102',
        'installed_version' => '0.9.5',
    ],
];

foreach ($testTenants as $tenant) {
    // Prüfe ob Mandant bereits existiert
    $existing = $pdo->prepare("SELECT id FROM bm_tenants WHERE domain = ?");
    $existing->execute([$tenant['domain']]);
    
    if ($existing->fetch()) {
        echo "⊘ Mandant '{$tenant['company_name']}' existiert bereits\n";
        continue;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO bm_tenants (
            tenant_name, company_name, domain, contact_email, contact_phone,
            tenant_status, trial_until, server_host, server_ip, installed_version,
            last_heartbeat
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $tenant['tenant_name'],
        $tenant['company_name'],
        $tenant['domain'],
        $tenant['contact_email'],
        $tenant['contact_phone'] ?? null,
        $tenant['tenant_status'],
        $tenant['trial_until'] ?? null,
        $tenant['server_host'] ?? null,
        $tenant['server_ip'] ?? null,
        $tenant['installed_version'] ?? '1.0.0',
        date('Y-m-d H:i:s'), // last_heartbeat
    ]);
    
    $tenantId = $pdo->lastInsertId();
    
    echo "✓ Mandant '{$tenant['company_name']}' erstellt (ID: $tenantId)\n";
    
    // Füge einige Lizenzen hinzu
    $modules = $pdo->query("SELECT id FROM bm_modules WHERE is_core = 0 LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($modules as $moduleId) {
        $stmt = $pdo->prepare("
            INSERT INTO bm_tenant_licenses (tenant_id, module_id, user_count, is_active, activated_at)
            VALUES (?, ?, ?, 1, ?)
        ");
        $stmt->execute([$tenantId, $moduleId, rand(1, 5), date('Y-m-d H:i:s')]);
    }
    
    // Füge Heartbeat hinzu
    $stmt = $pdo->prepare("
        INSERT INTO bm_tenant_heartbeats (
            tenant_id, php_version, database_size, user_count, active_modules,
            response_time, memory_usage, heartbeat_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $tenantId,
        '8.0.30',
        rand(5000, 50000), // KB
        rand(1, 10),
        count($modules),
        rand(50, 300), // ms
        rand(50, 200), // MB
        'online'
    ]);
}

echo "\n✓ Partner Console Seed abgeschlossen!\n";
echo "  Mandanten: " . count($testTenants) . "\n";
echo "\nÖffne: http://localhost:8002/partner-console\n";
