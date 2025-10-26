<?php
/**
 * Test Dashboard-Queries
 */

require __DIR__ . '/vendor/autoload.php';

use SysExperts\BusinessManager\Database\Database;

$config = require __DIR__ . '/config/database.php';
$db = new Database($config);

// Simuliere User mit Tenant 2
$tenantId = 2;
$userId = 3;

echo "=== Test Dashboard Queries für Tenant $tenantId ===\n\n";

try {
    $stats = [
        'total_users' => $db->fetchOne('SELECT COUNT(*) as count FROM users WHERE tenant_id = ? AND is_active = 1', [$tenantId])['count'] ?? 0,
        'total_customers' => $db->fetchOne('SELECT COUNT(*) as count FROM bm_customers WHERE tenant_id = ? AND is_active = 1', [$tenantId])['count'] ?? 0,
        'total_invoices' => $db->fetchOne('SELECT COUNT(*) as count FROM bm_invoices WHERE tenant_id = ?', [$tenantId])['count'] ?? 0,
        'pending_invoices' => $db->fetchOne('SELECT COUNT(*) as count FROM bm_invoices WHERE tenant_id = ? AND status IN ("draft", "sent")', [$tenantId])['count'] ?? 0,
        'total_revenue' => $db->fetchOne('SELECT SUM(total) as sum FROM bm_invoices WHERE tenant_id = ? AND status = "paid"', [$tenantId])['sum'] ?? 0,
        'pending_amount' => $db->fetchOne('SELECT SUM(total) as sum FROM bm_invoices WHERE tenant_id = ? AND status IN ("sent", "overdue")', [$tenantId])['sum'] ?? 0,
        'active_modules' => $db->fetchOne('SELECT COUNT(DISTINCT module_id) as count FROM bm_module_licenses WHERE tenant_id = ? AND user_id = ? AND is_enabled = 1', [$tenantId, $userId])['count'] ?? 0,
    ];
    
    echo "✓ Alle Queries erfolgreich!\n\n";
    print_r($stats);
    
} catch (Exception $e) {
    echo "✗ FEHLER: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}
