<?php
/**
 * Kalender-Modul im Marketplace registrieren + Benachrichtigung
 */

require __DIR__ . '/../vendor/autoload.php';

use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Notifications\NotificationService;

$config = require __DIR__ . '/../config/database.php';
$dbConfig = $config['connections'][$config['default']];

try {
    $dbPath = $dbConfig['database'];
    $pdo = new PDO("sqlite:$dbPath", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Prüfe ob Modul bereits existiert
    $stmt = $pdo->prepare("SELECT id FROM bm_modules WHERE code = ?");
    $stmt->execute(['calendar']);
    
    if ($stmt->fetch()) {
        echo "✓ Kalender-Modul existiert bereits\n";
        exit(0);
    }

    // Modul hinzufügen
    $stmt = $pdo->prepare("
        INSERT INTO bm_modules (
            code, name, description, category, price_per_user, 
            is_core, is_active, display_order
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $moduleName = 'Kalender & Termine';
    $moduleDescription = 'Professioneller Kalender mit Terminverwaltung. Erstellen Sie Events, Meetings und Termine. Mit Drag & Drop, verschiedenen Ansichten (Monat/Woche/Tag), Kategorien und Erinnerungen.';
    
    $stmt->execute([
        'calendar',
        $moduleName,
        $moduleDescription,
        'Produktivität',
        1.00,
        0, // nicht core
        1, // aktiv
        80
    ]);

    echo "✓ Kalender-Modul erfolgreich im Marketplace registriert!\n";
    echo "  Code: calendar\n";
    echo "  Name: $moduleName\n";
    echo "  Preis: 1€ pro Benutzer/Monat\n\n";

    // WICHTIG: Benachrichtigung an alle User senden!
    $db = new Database($config);
    $notificationService = new NotificationService($db);
    
    // Hole alle Tenants
    $tenants = $pdo->query("SELECT DISTINCT tenant_id FROM users")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tenants as $tenantId) {
        $notificationService->notifyNewModule(
            $tenantId,
            $moduleName,
            $moduleDescription
        );
    }
    
    echo "✓ Benachrichtigungen an alle User gesendet!\n";
    echo "  Tenants: " . count($tenants) . "\n";

} catch (PDOException $e) {
    die("✗ Fehler: " . $e->getMessage() . "\n");
}
