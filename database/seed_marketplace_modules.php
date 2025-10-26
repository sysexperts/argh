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

    echo "Füge Marketplace-Module hinzu...\n";

    // Prüfe ob Tabelle existiert
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='bm_modules'")->fetchAll();
    if (empty($tables)) {
        die("✗ Tabelle bm_modules existiert nicht. Bitte zuerst Migration ausführen.\n");
    }

    // Module definieren
    $modules = [
        [
            'code' => 'time-tracking',
            'name' => 'Zeiterfassung',
            'description' => 'Erfassen Sie Arbeitszeiten, Pausen und Überstunden. ArbZG-konform mit automatischen Warnungen.',
            'category' => 'Produktivität',
            'price_per_user' => 1.00,
            'is_core' => 0,
            'is_active' => 1,
            'display_order' => 10
        ],
        [
            'code' => 'invoices',
            'name' => 'Rechnungen',
            'description' => 'Erstellen und verwalten Sie Rechnungen. Mit PDF-Export und Statusverfolgung.',
            'category' => 'Finanzen',
            'price_per_user' => 1.00,
            'is_core' => 0,
            'is_active' => 1,
            'display_order' => 20
        ],
        [
            'code' => 'customers',
            'name' => 'Kundenverwaltung',
            'description' => 'Verwalten Sie Ihre Kunden zentral. Mit Kontaktdaten, Notizen und Historie.',
            'category' => 'CRM',
            'price_per_user' => 1.00,
            'is_core' => 0,
            'is_active' => 1,
            'display_order' => 30
        ],
        [
            'code' => 'projects',
            'name' => 'Projektverwaltung',
            'description' => 'Organisieren Sie Projekte, Aufgaben und Teams. Mit Zeiterfassung und Budgetverfolgung.',
            'category' => 'Produktivität',
            'price_per_user' => 1.00,
            'is_core' => 0,
            'is_active' => 1,
            'display_order' => 40
        ],
        [
            'code' => 'documents',
            'name' => 'Dokumentenverwaltung',
            'description' => 'Speichern und organisieren Sie Dokumente sicher. Mit Versionierung und Freigaben.',
            'category' => 'Organisation',
            'price_per_user' => 1.00,
            'is_core' => 0,
            'is_active' => 1,
            'display_order' => 50
        ],
        [
            'code' => 'calendar',
            'name' => 'Kalender & Termine',
            'description' => 'Planen Sie Termine und Meetings. Mit Erinnerungen und Team-Kalender.',
            'category' => 'Organisation',
            'price_per_user' => 1.00,
            'is_core' => 0,
            'is_active' => 1,
            'display_order' => 60
        ],
    ];

    $inserted = 0;
    foreach ($modules as $module) {
        // Prüfe ob Modul bereits existiert
        $stmt = $pdo->prepare("SELECT id FROM bm_modules WHERE code = ?");
        $stmt->execute([$module['code']]);
        $existing = $stmt->fetch();

        if (!$existing) {
            $stmt = $pdo->prepare("
                INSERT INTO bm_modules (
                    code, name, description, category, 
                    price_per_user, is_core, is_active, display_order
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $module['code'],
                $module['name'],
                $module['description'],
                $module['category'],
                $module['price_per_user'],
                $module['is_core'],
                $module['is_active'],
                $module['display_order']
            ]);
            $inserted++;
            echo "  ✓ {$module['name']} hinzugefügt\n";
        } else {
            echo "  - {$module['name']} existiert bereits\n";
        }
    }

    echo "\n✓ {$inserted} Module hinzugefügt!\n";
    
} catch (PDOException $e) {
    die("✗ Fehler: " . $e->getMessage() . "\n");
}
