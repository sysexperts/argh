<?php
require __DIR__ . '/vendor/autoload.php';

$pdo = new PDO('sqlite:database/business_manager.sqlite');

$sql = "
CREATE TABLE IF NOT EXISTS time_entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_time_entries_user_id ON time_entries(user_id);
CREATE INDEX IF NOT EXISTS idx_time_entries_start_time ON time_entries(start_time);
";

try {
    $pdo->exec($sql);
    echo "✅ Tabelle time_entries erfolgreich erstellt!\n";
    
    // Prüfe ob Tabelle existiert
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='time_entries'")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "✅ Tabelle time_entries existiert jetzt!\n";
        
        // Zeige Struktur
        $columns = $pdo->query("PRAGMA table_info(time_entries)")->fetchAll(PDO::FETCH_ASSOC);
        echo "\nTabellen-Struktur:\n";
        foreach ($columns as $col) {
            echo "  - {$col['name']} ({$col['type']})\n";
        }
    } else {
        echo "❌ Fehler: Tabelle wurde nicht erstellt!\n";
    }
} catch (PDOException $e) {
    echo "❌ Fehler: " . $e->getMessage() . "\n";
}
