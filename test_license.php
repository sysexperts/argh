<?php
/**
 * Test-Script für Lizenzprüfung
 */

require __DIR__ . '/vendor/autoload.php';

use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\LicenseChecker;

// DB-Config laden
$config = require __DIR__ . '/config/database.php';
$db = new Database($config);

// LicenseChecker erstellen
$checker = new LicenseChecker($db);

// Test-User (ID 1 = admin@sys-experts.de)
$userId = 1;
$userRole = 'admin';

echo "=== Lizenzprüfung für User ID: $userId (Role: $userRole) ===\n\n";

// Test verschiedene Module
$modules = [
    'invoices' => 'Rechnungen',
    'customers' => 'Kunden',
    'time-tracking' => 'Zeiterfassung',
    'projects' => 'Projekte',
    'documents' => 'Dokumente',
];

foreach ($modules as $code => $name) {
    $hasLicense = $checker->hasLicense($userId, $code, $userRole);
    $status = $hasLicense ? '✅ Zugriff erlaubt' : '❌ Keine Lizenz';
    echo "$name ($code): $status\n";
}

echo "\n=== Test abgeschlossen ===\n";
