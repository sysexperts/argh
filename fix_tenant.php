<?php
/**
 * Setze user@sys-experts.de auf tenant_id = 2
 */

require __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config/database.php';
$dbConfig = $config['connections'][$config['default']];

try {
    $dbPath = $dbConfig['database'];
    $pdo = new PDO("sqlite:$dbPath", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("UPDATE users SET tenant_id = 2 WHERE email = 'user@sys-experts.de'");
    
    echo "✓ User 'user@sys-experts.de' ist jetzt Tenant 2\n";
    echo "✓ Beide User haben jetzt getrennte Daten!\n";
    
} catch (PDOException $e) {
    die("Fehler: " . $e->getMessage() . "\n");
}
