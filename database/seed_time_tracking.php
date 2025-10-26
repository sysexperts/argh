<?php
require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/database.php';
$dbConfig = $config['connections'][$config['default']];

try {
    if ($dbConfig['driver'] === 'sqlite') {
        $dbPath = $dbConfig['database'];
        $pdo = new PDO("sqlite:$dbPath", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } else {
        die("✗ Nur SQLite wird unterstützt.\n");
    }

    $stmt = $pdo->prepare("SELECT id FROM users LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("✗ Kein Benutzer gefunden. Bitte zuerst Test-User anlegen.\n");
    }

    $userId = $user['id'];
    $tenantId = 1;

    echo "Erstelle Testdaten für Zeiterfassung...\n";

    for ($i = 0; $i < 10; $i++) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $startTime = date('Y-m-d 08:00:00', strtotime("-$i days"));
        $endTime = date('Y-m-d 17:30:00', strtotime("-$i days"));
        
        $stmt = $pdo->prepare("
            INSERT INTO time_entries (user_id, tenant_id, date, start_time, end_time, total_hours, overtime_hours, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')
        ");
        
        $totalHours = 8.5 + ($i % 3);
        $overtimeHours = max(0, $totalHours - 8.0);
        
        $stmt->execute([$userId, $tenantId, $date, $startTime, $endTime, $totalHours, $overtimeHours]);
        
        $entryId = $pdo->lastInsertId();
        
        $breakStart = date('Y-m-d 12:00:00', strtotime("-$i days"));
        $breakEnd = date('Y-m-d 12:30:00', strtotime("-$i days"));
        
        $stmt = $pdo->prepare("
            INSERT INTO time_breaks (time_entry_id, start_time, end_time, duration_minutes, break_type)
            VALUES (?, ?, ?, 30, 'lunch')
        ");
        $stmt->execute([$entryId, $breakStart, $breakEnd]);
    }

    echo "✓ Testdaten erfolgreich erstellt!\n";
    echo "  10 Zeiteinträge mit Pausen angelegt\n";
    
} catch (PDOException $e) {
    die("✗ Fehler: " . $e->getMessage() . "\n");
}
