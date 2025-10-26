<?php
/**
 * Migration Runner
 * Führt eine SQL-Migration aus
 */

require __DIR__ . '/../vendor/autoload.php';

// Datenbank-Konfiguration laden
$config = require __DIR__ . '/../config/database.php';
$dbConfig = $config['connections'][$config['default']];

try {
    // PDO-Verbindung erstellen
    if ($dbConfig['driver'] === 'mysql') {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $dbConfig['host'],
            $dbConfig['database']
        );
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } elseif ($dbConfig['driver'] === 'sqlite') {
        $dbPath = $dbConfig['database'];
        // Erstelle Verzeichnis falls nicht vorhanden
        $dbDir = dirname($dbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        $pdo = new PDO("sqlite:$dbPath", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } else {
        die("✗ Nicht unterstützter Datenbank-Treiber: {$dbConfig['driver']}\n");
    }

    // Migration-Datei laden
    $migrationFile = $argv[1] ?? 'database/migrations/003_create_users_table.sql';
    $fullPath = __DIR__ . '/../' . $migrationFile;
    
    if (!file_exists($fullPath)) {
        die("Migration-Datei nicht gefunden: $fullPath\n");
    }

    $sql = file_get_contents($fullPath);
    
    // SQL ausführen
    $pdo->exec($sql);
    
    echo "✓ Migration erfolgreich ausgeführt: $migrationFile\n";
    
} catch (PDOException $e) {
    die("✗ Fehler bei Migration: " . $e->getMessage() . "\n");
}
