# Update-System Dokumentation

## Übersicht

Das Update-System ermöglicht zentrale Verwaltung und Verteilung von Updates an alle Kunden-Instanzen.

## Komponenten

### 1. Update-Verwaltung (Partner Console)
- **Zugriff:** `/updates` (für alle Admins)
- **Partner Console:** `/partner-console` (nur sys-experts.de)
- **Features:**
  - Übersicht verfügbarer Updates
  - Update-Installation
  - Update-Historie
  - Release-Management

### 2. Update-API
Zentrale API für Kunden-Instanzen:

#### Endpunkte:
```
GET  /api/updates/check?current_version=1.0.0&tenant_id=1
GET  /api/updates/download/{version}
POST /api/updates/heartbeat
```

#### Authentifizierung:
```
X-API-Key: sys-experts-api-key-{tenant_id}
```

### 3. Auto-Updater Client
Script für Kunden-Server: `bin/auto-update.php`

#### Verwendung:
```bash
# Prüfe auf Updates
php bin/auto-update.php check

# Installiere Update
php bin/auto-update.php install 1.1.0

# Sende Heartbeat
php bin/auto-update.php heartbeat
```

#### Konfiguration:
Umgebungsvariablen:
```bash
TENANT_ID=1
UPDATE_API_KEY=sys-experts-api-key-1
```

### 4. E-Mail-Benachrichtigungen
Automatische Benachrichtigungen bei:
- Neuen Releases
- Sicherheitsupdates
- Verpassten Updates

#### Cron-Job:
```bash
# Täglich um 9:00 Uhr
0 9 * * * /usr/bin/php /path/to/bin/cron-update-check.php
```

## Release-Typen

### Feature
Neue Funktionen und Verbesserungen

### Bugfix
Fehlerbehebungen

### Security
Sicherheitsupdates (verpflichtend!)

### Hotfix
Dringende Fehlerbehebungen

## Workflow

### 1. Release erstellen
```php
$pdo->prepare("
    INSERT INTO bm_releases (
        version, release_date, release_type, title, description, changelog,
        is_security_update, is_breaking_change, requires_migration
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
")->execute([
    '1.1.0',
    '2025-02-01',
    'feature',
    'Neues Feature',
    'Beschreibung',
    'Changelog',
    0, // is_security_update
    0, // is_breaking_change
    1  // requires_migration
]);
```

### 2. Benachrichtigungen versenden
```php
$notificationService = new UpdateNotificationService($db);
$notificationService->notifyNewRelease($release);
```

### 3. Kunden installieren Update
- Automatisch via Cron-Job (bei Sicherheitsupdates)
- Manuell über `/updates` Interface
- Via CLI: `php bin/auto-update.php install 1.1.0`

### 4. Heartbeat
Kunden-Instanzen senden regelmäßig Status:
```bash
# Alle 5 Minuten
*/5 * * * * /usr/bin/php /path/to/bin/auto-update.php heartbeat
```

## Sicherheit

### API-Keys
- Pro Tenant: `sys-experts-api-key-{tenant_id}`
- Master-Key: `sys-experts-master-api-key`
- TODO: In `bm_tenants` Tabelle speichern

### Backup
Vor jedem Update wird automatisch ein Backup erstellt:
```
backups/2025-01-15_10-30-00_v1.0.0/
```

### Rollback
Bei Problemen:
1. Backup wiederherstellen
2. Version in `config/version.php` zurücksetzen
3. Heartbeat senden

## Verpflichtende Sicherheitsupdates

Gemäß Nutzungsbedingungen:
- Sicherheitsupdates MÜSSEN installiert werden
- Nach 3 Mahnungen: Zugang gesperrt
- Automatische E-Mail-Warnungen

## Monitoring

### Partner Console
- Übersicht aller Mandanten
- Installierte Versionen
- Letzte Heartbeats
- Verpasste Updates

### Heartbeat-Daten
- PHP-Version
- Datenbankgröße
- Benutzeranzahl
- Aktive Module
- Response-Zeit
- Speichernutzung

## Beispiel: Neues Release

```php
// 1. Release erstellen
$release = [
    'version' => '1.2.0',
    'release_date' => '2025-03-01',
    'release_type' => 'feature',
    'title' => 'Kalender-Modul',
    'description' => 'Neues Kalender-Modul mit FullCalendar',
    'changelog' => "- Kalender hinzugefügt\n- Termine erstellen\n- Drag & Drop",
    'is_security_update' => 0,
    'is_breaking_change' => 0,
    'requires_migration' => 1,
];

// 2. In DB speichern
$stmt = $pdo->prepare("INSERT INTO bm_releases ...");
$stmt->execute([...]);

// 3. Benachrichtigungen senden
$notificationService->notifyNewRelease($release);

// 4. Kunden installieren Update
// - Automatisch via Cron
// - Oder manuell über UI
```

## Troubleshooting

### Update schlägt fehl
1. Prüfe Logs in `storage/logs/`
2. Prüfe Backup in `backups/`
3. Rollback durchführen
4. Support kontaktieren

### API nicht erreichbar
1. Prüfe `UPDATE_API_KEY`
2. Prüfe Firewall
3. Prüfe DNS

### E-Mails kommen nicht an
1. Prüfe `config/mail.php`
2. Prüfe SMTP-Credentials
3. Prüfe Spam-Ordner

## Roadmap

- [ ] Automatische Rollback-Funktion
- [ ] Staged Rollouts (Beta → Stable)
- [ ] Update-Zeitfenster konfigurierbar
- [ ] Webhook-Benachrichtigungen
- [ ] Update-Statistiken & Analytics
