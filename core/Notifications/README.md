# Benachrichtigungssystem

## Übersicht

Das Benachrichtigungssystem ermöglicht es, Benutzer über wichtige Ereignisse zu informieren.

## Features

- ✅ Benachrichtigungen an einzelne User oder alle User eines Tenants
- ✅ Live-Widget im Header mit Badge
- ✅ Auto-Refresh alle 30 Sekunden
- ✅ "Als gelesen markieren" Funktion
- ✅ Verschiedene Typen mit Icons
- ✅ Links zu relevanten Seiten
- ✅ Relative Zeitangaben

## Verwendung

### Benachrichtigung erstellen

```php
use SysExperts\BusinessManager\Notifications\NotificationService;

$notificationService = new NotificationService($db);

// An einzelnen User
$notificationService->create(
    $tenantId,      // Mandanten-ID
    $userId,        // User-ID
    'ticket',       // Typ
    'Neues Ticket', // Titel
    'Ticket #123 wurde erstellt', // Nachricht
    '/helpdesk/123', // Optional: Link
    'support_agent'  // Optional: Icon
);

// An alle User eines Tenants
$notificationService->create(
    $tenantId,
    null,           // null = an alle User
    'system',
    'Wartung',
    'System-Wartung am Sonntag'
);
```

### Marketplace-Benachrichtigungen

**WICHTIG:** Bei jedem neuen Modul im Marketplace MUSS eine Benachrichtigung versendet werden!

```php
$notificationService->notifyNewModule(
    $tenantId,
    'Modul-Name',
    'Modul-Beschreibung'
);
```

Dies sendet automatisch eine formatierte Benachrichtigung an alle User des Tenants.

## Benachrichtigungs-Typen

| Typ | Icon | Verwendung |
|-----|------|------------|
| `new_module` | extension | Neues Modul im Marketplace |
| `ticket` | support_agent | Ticket-Updates |
| `invoice` | receipt_long | Rechnungs-Updates |
| `customer` | person | Kunden-Updates |
| `system` | info | System-Meldungen |
| `warning` | warning | Warnungen |
| `success` | check_circle | Erfolgs-Meldungen |
| `error` | error | Fehler-Meldungen |

## API-Endpunkte

- `GET /api/notifications` - Liste aller Benachrichtigungen
- `POST /api/notifications/{id}/read` - Als gelesen markieren
- `POST /api/notifications/read-all` - Alle als gelesen markieren
- `DELETE /api/notifications/{id}` - Löschen

## Automatische Bereinigung

Alte gelesene Benachrichtigungen können automatisch gelöscht werden:

```php
$notificationService->deleteOld(30); // Löscht gelesene Benachrichtigungen älter als 30 Tage
```

Dies sollte als Cronjob eingerichtet werden.

## Best Practices

1. **Immer aussagekräftige Titel verwenden**
2. **Links zu relevanten Seiten hinzufügen**
3. **Passende Icons wählen**
4. **Nicht zu viele Benachrichtigungen** (nur wichtige Events)
5. **Bei neuen Modulen IMMER benachrichtigen** ⚠️
