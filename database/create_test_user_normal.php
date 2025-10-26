<?php
/**
 * Normalen Test-Benutzer erstellen (kein Admin)
 */

require __DIR__ . '/../vendor/autoload.php';

// Datenbank-Konfiguration laden
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

    // Test-Benutzer-Daten
    $email = 'user@sys-experts.de';
    $password = 'user123';
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    // Prüfe ob Benutzer bereits existiert
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo "✓ Benutzer existiert bereits: $email\n";
        exit(0);
    }

    // Benutzer erstellen
    $stmt = $pdo->prepare("
        INSERT INTO users (
            tenant_id, email, password_hash, first_name, last_name, 
            role, is_active, email_verified
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        1, // tenant_id
        $email,
        $passwordHash,
        'Normal',
        'User',
        'user', // WICHTIG: Nicht admin!
        1, // is_active
        1  // email_verified
    ]);
    
    $userId = $pdo->lastInsertId();
    
    echo "✓ Normaler Test-Benutzer erfolgreich erstellt!\n";
    echo "  E-Mail: $email\n";
    echo "  Passwort: $password\n";
    echo "  Rolle: user (kein Admin)\n";
    echo "  User-ID: $userId\n";
    echo "\n";
    echo "⚠️  Dieser User hat KEINE Lizenzen!\n";
    echo "   Zum Testen der Lizenzprüfung perfekt.\n";
    
} catch (PDOException $e) {
    die("✗ Fehler: " . $e->getMessage() . "\n");
}
