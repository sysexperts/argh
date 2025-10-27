<?php
/**
 * Seed Audit-Logs Modul
 */

require __DIR__ . '/../vendor/autoload.php';

use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Notifications\NotificationService;

$config = require __DIR__ . '/../config/database.php';
$db = new Database($config);
$pdo = $db->getConnection();

echo "=== Seed Audit-Logs Modul ===\n\n";

// 1. Prüfe ob Modul bereits existiert
$existing = $pdo->prepare("SELECT id FROM bm_modules WHERE code = ?");
$existing->execute(['audit-logs']);

if ($existing->fetch()) {
    echo "⊘ Modul 'audit-logs' existiert bereits\n";
    exit(0);
}

// 2. Registriere Modul in bm_modules
echo "1. Registriere Modul...\n";
$stmt = $pdo->prepare("
    INSERT INTO bm_modules (
        code, name, description, category, price_per_user, 
        is_core, is_active, display_order
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    'audit-logs',
    'Audit-Logs',
    'GoBD-konforme, revisionssichere Protokollierung aller Systemaktivitäten. Unveränderbar und mit Checksumme für maximale Sicherheit.',
    'Compliance',
    1.00,  // 1€ pro User pro Monat
    0,     // Nicht Core (kann deaktiviert werden)
    1,     // Aktiv
    200    // Display Order
]);

$moduleId = $pdo->lastInsertId();
echo "   ✓ Modul registriert (ID: $moduleId)\n\n";

// 3. Aktiviere für Admin-User (User ID 1)
echo "2. Aktiviere für Admin-User...\n";
$stmt = $pdo->prepare("
    INSERT INTO bm_module_licenses (user_id, module_id, is_enabled)
    VALUES (?, ?, 1)
");
$stmt->execute([1, $moduleId]);
echo "   ✓ Lizenz für Admin aktiviert\n\n";

// 4. Sende Benachrichtigungen an ALLE User
echo "3. Sende Benachrichtigungen...\n";
$notificationService = new NotificationService($db);

// Hole alle Tenants
$tenants = $pdo->query("SELECT DISTINCT tenant_id FROM users WHERE tenant_id IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

if (empty($tenants)) {
    echo "   ⚠ Keine Tenants gefunden, sende an User direkt\n";
    // Fallback: Sende an alle User
    $users = $pdo->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($users as $userId) {
        $notificationService->create(
            $userId,
            'Neues Modul verfügbar: Audit-Logs',
            'Das neue Audit-Logs Modul ist jetzt verfügbar! GoBD-konforme Protokollierung aller Systemaktivitäten.',
            'new_module',
            '/marketplace',
            'extension'
        );
    }
    echo "   ✓ Benachrichtigungen an " . count($users) . " User gesendet\n";
} else {
    foreach ($tenants as $tenantId) {
        $notificationService->notifyNewModule(
            $tenantId,
            'Audit-Logs',
            'GoBD-konforme, revisionssichere Protokollierung aller Systemaktivitäten.'
        );
    }
    echo "   ✓ Benachrichtigungen an " . count($tenants) . " Tenant(s) gesendet\n";
}

echo "\n✓ Audit-Logs Modul erfolgreich hinzugefügt!\n";
echo "\nDas Modul ist jetzt:\n";
echo "- Im Marketplace sichtbar\n";
echo "- Für Admin-User aktiviert\n";
echo "- Benachrichtigungen wurden versendet\n";
echo "\nÖffne: http://localhost:8002/audit-logs\n";
