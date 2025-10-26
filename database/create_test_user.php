<?php
/**
 * Test-Benutzer erstellen
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
        $pdo = new PDO("sqlite:$dbPath", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } else {
        die("✗ Nicht unterstützter Datenbank-Treiber: {$dbConfig['driver']}\n");
    }

    // Test-Benutzer-Daten
    $email = 'admin@sys-experts.de';
    $password = 'admin123';
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
        'Admin',
        'User',
        'admin',
        1, // is_active
        1  // email_verified
    ]);
    
    echo "✓ Test-Benutzer erfolgreich erstellt!\n";
    echo "  E-Mail: $email\n";
    echo "  Passwort: $password\n";
    
} catch (PDOException $e) {
    die("✗ Fehler: " . $e->getMessage() . "\n");
}
