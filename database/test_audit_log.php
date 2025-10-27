<?php
require __DIR__ . '/../vendor/autoload.php';

use SysExperts\BusinessManager\AuditLog\AuditLogger;

echo "=== Test Audit-Log System ===\n\n";

// Test 1: Create Log
echo "1. Erstelle Test-Log (Rechnung erstellt)...\n";
AuditLogger::logCreate(
    1, // User ID
    'invoice',
    101,
    'Rechnung RE-2025-0001 erstellt',
    ['customer' => 'Test GmbH', 'amount' => 1500.00]
);
echo "   ✓ Create-Log erstellt\n\n";

// Test 2: Update Log
echo "2. Erstelle Update-Log (Rechnung geändert)...\n";
AuditLogger::logUpdate(
    1,
    'invoice',
    101,
    'Rechnung RE-2025-0001 geändert: Betrag aktualisiert',
    ['amount' => 1500.00],
    ['amount' => 1750.00]
);
echo "   ✓ Update-Log erstellt\n\n";

// Test 3: Login Log
echo "3. Erstelle Login-Log...\n";
AuditLogger::logLogin(1, true);
echo "   ✓ Login-Log erstellt\n\n";

// Test 4: Security Event
echo "4. Erstelle Security-Event...\n";
AuditLogger::logSecurityEvent(
    1,
    'Verdächtiger Login-Versuch von unbekannter IP',
    'warning'
);
echo "   ✓ Security-Event erstellt\n\n";

// Test 5: Hole Logs
echo "5. Hole alle Logs...\n";
$config = require __DIR__ . '/../config/database.php';
$db = new \SysExperts\BusinessManager\Database\Database($config);
$auditService = new \SysExperts\BusinessManager\AuditLog\AuditLogService($db);

$logs = $auditService->getLogs([], 10, 0);
echo "   Gefundene Logs: " . count($logs) . "\n";

foreach ($logs as $log) {
    echo sprintf(
        "   - [%s] %s: %s (User: %s)\n",
        $log['created_at'],
        $log['action'],
        $log['description'],
        $log['user_name']
    );
}

echo "\n✓ Audit-Log System funktioniert!\n";
echo "\nÖffne: http://localhost:8002/audit-logs\n";
