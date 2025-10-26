<?php
/**
 * Helpdesk-Modul im Marketplace registrieren
 */

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/database.php';
$dbConfig = $config['connections'][$config['default']];

try {
    $dbPath = $dbConfig['database'];
    $pdo = new PDO("sqlite:$dbPath", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Prüfe ob Modul bereits existiert
    $stmt = $pdo->prepare("SELECT id FROM bm_modules WHERE code = ?");
    $stmt->execute(['helpdesk']);
    
    if ($stmt->fetch()) {
        echo "✓ Helpdesk-Modul existiert bereits\n";
        exit(0);
    }

    // Modul hinzufügen
    $stmt = $pdo->prepare("
        INSERT INTO bm_modules (
            code, name, description, category, price_per_user, 
            is_core, is_active, display_order
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        'helpdesk',
        'Helpdesk & Ticketing',
        'Professionelles Ticketing-System für Support und Kundenservice. Verwalten Sie Tickets, weisen Sie sie Mitarbeitern zu, tracken Sie Status und Prioritäten. Perfekt für IT-Support, Kundenservice und interne Anfragen.',
        'Support',
        1.00,
        0, // nicht core
        1, // aktiv
        70
    ]);

    echo "✓ Helpdesk-Modul erfolgreich im Marketplace registriert!\n";
    echo "  Code: helpdesk\n";
    echo "  Name: Helpdesk & Ticketing\n";
    echo "  Preis: 1€ pro Benutzer/Monat\n";

} catch (PDOException $e) {
    die("✗ Fehler: " . $e->getMessage() . "\n");
}
