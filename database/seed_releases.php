<?php
/**
 * Seed Test-Releases
 */

require __DIR__ . '/../vendor/autoload.php';

$pdo = new PDO('sqlite:' . __DIR__ . '/business_manager.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Seed Releases ===\n\n";

// Aktuelle Version speichern
$currentVersion = '1.0.0';
$stmt = $pdo->prepare("
    INSERT OR REPLACE INTO bm_tenant_versions (tenant_id, module_code, version, deployed_by, deployed_at)
    VALUES (1, 'core', ?, 'System', ?)
");
$stmt->execute([$currentVersion, date('Y-m-d H:i:s')]);

// Test-Releases
$releases = [
    [
        'version' => '1.0.0',
        'release_date' => '2025-01-01',
        'release_type' => 'feature',
        'title' => 'Initiales Release',
        'description' => 'Erste stabile Version des Business Managers',
        'changelog' => "- Dashboard\n- Benutzerverwaltung\n- Marketplace\n- Rechnungen\n- Kunden\n- Zeiterfassung",
        'is_security_update' => 0,
        'is_breaking_change' => 0,
        'requires_migration' => 0,
    ],
    [
        'version' => '1.1.0',
        'release_date' => '2025-02-01',
        'release_type' => 'feature',
        'title' => 'Helpdesk & Ticketing',
        'description' => 'Neues Helpdesk-Modul für Support-Tickets',
        'changelog' => "- Helpdesk-Modul hinzugefügt\n- Ticket-System\n- Kommentare\n- Status-Verwaltung\n- Prioritäten",
        'is_security_update' => 0,
        'is_breaking_change' => 0,
        'requires_migration' => 1,
    ],
    [
        'version' => '1.2.0',
        'release_date' => '2025-03-01',
        'release_type' => 'feature',
        'title' => 'Kalender & Termine',
        'description' => 'Kalender-Modul mit FullCalendar Integration',
        'changelog' => "- Kalender-Modul\n- Termine erstellen/bearbeiten\n- Drag & Drop\n- Kategorien\n- Erinnerungen",
        'is_security_update' => 0,
        'is_breaking_change' => 0,
        'requires_migration' => 1,
    ],
    [
        'version' => '1.2.1',
        'release_date' => '2025-03-15',
        'release_type' => 'security',
        'title' => 'Sicherheitsupdate',
        'description' => 'Kritisches Sicherheitsupdate - Installation erforderlich!',
        'changelog' => "- XSS-Schwachstelle in Kommentaren behoben\n- SQL-Injection-Schutz verbessert\n- Session-Handling optimiert",
        'is_security_update' => 1,
        'is_breaking_change' => 0,
        'requires_migration' => 0,
    ],
    [
        'version' => '1.3.0',
        'release_date' => '2025-04-01',
        'release_type' => 'feature',
        'title' => 'Partner Console',
        'description' => 'Zentrale Verwaltung für sys-experts.de',
        'changelog' => "- Partner Console für Mandanten-Verwaltung\n- Lizenz-Management\n- Monitoring & Heartbeat\n- Release-Management",
        'is_security_update' => 0,
        'is_breaking_change' => 0,
        'requires_migration' => 1,
    ],
];

foreach ($releases as $release) {
    // Prüfe ob Release bereits existiert
    $existing = $pdo->prepare("SELECT id FROM bm_releases WHERE version = ?");
    $existing->execute([$release['version']]);
    
    if ($existing->fetch()) {
        echo "⊘ Release {$release['version']} existiert bereits\n";
        continue;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO bm_releases (
            version, release_date, release_type, title, description, changelog,
            is_security_update, is_breaking_change, requires_migration
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $release['version'],
        $release['release_date'],
        $release['release_type'],
        $release['title'],
        $release['description'],
        $release['changelog'],
        $release['is_security_update'],
        $release['is_breaking_change'],
        $release['requires_migration'],
    ]);
    
    echo "✓ Release {$release['version']} erstellt\n";
}

echo "\n✓ Releases Seed abgeschlossen!\n";
echo "  Aktuelle Version: $currentVersion\n";
echo "  Releases: " . count($releases) . "\n";
echo "\nÖffne: http://localhost:8002/updates\n";
