<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');

$sql = "
CREATE TABLE IF NOT EXISTS user_sessions (
    session_id VARCHAR(64) PRIMARY KEY,
    user_id INTEGER NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    last_activity DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_user_sessions_user_id ON user_sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_user_sessions_last_activity ON user_sessions(last_activity);
";

$pdo->exec($sql);
echo "✅ Tabelle user_sessions erfolgreich erstellt!\n";

// Prüfe ob Tabelle existiert
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='user_sessions'")->fetchAll(PDO::FETCH_COLUMN);
if (count($tables) > 0) {
    echo "✅ Tabelle user_sessions existiert jetzt!\n";
} else {
    echo "❌ Fehler: Tabelle wurde nicht erstellt!\n";
}
