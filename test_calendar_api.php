<?php
/**
 * Test Calendar API
 */

require __DIR__ . '/vendor/autoload.php';

use SysExperts\BusinessManager\Database\Database;

$config = require __DIR__ . '/config/database.php';
$db = new Database($config);

echo "=== Test Calendar Event Creation ===\n\n";

try {
    // Simuliere Event-Erstellung
    $eventData = [
        'tenant_id' => 1,
        'title' => 'Test Meeting',
        'description' => 'Test Beschreibung',
        'location' => 'Büro',
        'start_datetime' => '2025-01-15 10:00:00',
        'end_datetime' => '2025-01-15 11:00:00',
        'is_all_day' => 0,
        'category' => 'meeting',
        'color' => '#14b8a6',
        'status' => 'planned',
        'customer_id' => null,
        'created_by' => 1,
    ];
    
    echo "Versuche Event zu erstellen...\n";
    
    $eventId = $db->insert('bm_calendar_events', $eventData);
    
    echo "✓ Event erfolgreich erstellt!\n";
    echo "  ID: $eventId\n";
    echo "  Titel: {$eventData['title']}\n";
    
    // Prüfe ob Event in DB ist
    $event = $db->fetchOne('SELECT * FROM bm_calendar_events WHERE id = ?', [$eventId]);
    
    if ($event) {
        echo "\n✓ Event in Datenbank gefunden:\n";
        print_r($event);
    }
    
} catch (\Exception $e) {
    echo "✗ FEHLER: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}
