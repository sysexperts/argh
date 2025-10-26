# 🚀 Business Manager

Modulare mandantenfähige Business-Software von **sys-experts.de**

## 📋 Übersicht

Business Manager ist eine flexible, modulare Business-Software mit folgenden Kernmerkmalen:

- **Multi-Tenant**: Jeder Mandant erhält eigene Installation + eigene Datenbank
- **Modular**: Kleinteilige Module (Rechnungen, Buchhaltung, Zeiterfassung, etc.)
- **Lizenzbasiert**: 1€ pro Modul pro Benutzer pro Monat
- **White-Label**: Logo, Farben, Produktname anpassbar
- **GoBD-konform**: Revisionssichere Audit-Logs
- **Modern UI**: Dark Mode, responsive Design mit TailwindCSS

## 🛠️ Tech-Stack

- **PHP 8.1+**
- **Slim Framework 4** (Routing, Middleware)
- **PHP-DI** (Dependency Injection)
- **SQLite** (Development) / MySQL (Production)
- **TailwindCSS** (UI Framework)
- **Alpine.js** (JavaScript Interaktivität)
- **TCPDF** (PDF-Generierung)
- **Composer** (Dependency Management)

## 📦 Installation

### Voraussetzungen

- PHP 8.1 oder höher
- Composer
- SQLite (Development) oder MySQL (Production)
- Apache/Nginx mit mod_rewrite
- XAMPP empfohlen für Windows

### Setup (Schnellstart)

1. **Dependencies installieren**
   ```bash
   composer install
   ```

2. **Umgebungsvariablen konfigurieren**
   ```bash
   copy .env.example .env
   ```
   
   Passe `.env` an (DB-Verbindung, etc.)

3. **Datenbank initialisieren**
   ```bash
   php setup_database.php
   php database/create_test_user.php
   php database/seed_marketplace_modules.php
   php create_invoice_tables.php
   php create_customers_table.php
   php create_settings_table.php
   ```

4. **Zeiterfassungs-Tabellen erstellen**
   ```bash
   php database/run_migration.php database/migrations/004_create_time_tracking_tables.sql
   ```

5. **Webserver starten**
   - XAMPP: Projekt in `htdocs/` ablegen
   - DocumentRoot: `public/`
   - URL: `http://localhost/sys-experts-toolbox/public/`

6. **Login**
   - E-Mail: `admin@sys-experts.de`
   - Passwort: `admin123`

## 📁 Projektstruktur

```
sys-experts-toolbox/
├── bootstrap/          # App-Bootstrap & DI-Container
├── config/             # Konfigurationsdateien
├── core/               # Core-System
│   ├── Auth/           # Authentifizierung, Session, User
│   ├── Customers/      # Kundenverwaltung
│   ├── Database/       # PDO-Wrapper & Query Builder
│   ├── Invoices/       # Rechnungserstellung
│   ├── Modules/        # Modul- & Lizenzverwaltung
│   ├── Navigation/     # Dynamische Navigation
│   ├── Settings/       # System-Einstellungen
│   ├── TimeTracking/   # Zeiterfassung (ArbZG-konform)
│   └── Users/          # Benutzerverwaltung
├── database/           # Migrationen, Seeds, Setup-Scripts
├── public/             # Webroot (index.php, assets)
│   └── assets/         # CSS, JS, Images
├── resources/          # Views (PHP Templates)
│   └── views/
│       ├── auth/       # Login, Register
│       ├── customers/  # Kundenverwaltung
│       ├── invoices/   # Rechnungen
│       ├── layouts/    # Layout-Templates
│       ├── modules/    # Marketplace
│       ├── settings/   # Einstellungen
│       ├── time_tracking/  # Zeiterfassung
│       └── users/      # Benutzerverwaltung
├── routes/             # Route-Definitionen
├── storage/            # Logs, Cache, Uploads
├── .env.example        # Umgebungsvariablen Template
├── composer.json       # PHP Dependencies
└── README.md
```

## ✅ Implementierte Features

### Core-System (immer verfügbar)
- ✅ **Authentifizierung** - Login, Logout, Session-Management
- ✅ **Dashboard** - Übersicht mit KPIs und Schnellzugriffen
- ✅ **Benutzerverwaltung** - CRUD, Rollen, Aktivierung/Deaktivierung
- ✅ **Lizenzverwaltung** - Pro User, Pro Modul
- ✅ **Marketplace** - Module aktivieren/deaktivieren
- ✅ **Einstellungen** - Firmendaten, Bank, Rechnungseinstellungen
- ✅ **Navigation** - Dynamisch basierend auf Lizenzen
- ✅ **Dark Mode** - Vollständig implementiert

### Business-Module (lizenzpflichtig)
- ✅ **Rechnungen** - Erstellen, Positionen, Status, PDF-Export
- ✅ **Kunden** - CRUD, Kontaktdaten, Adressen
- ✅ **Zeiterfassung** - Start/Stop, Pausen, ArbZG-Warnungen, Export
- ⏳ **Projekte** - Geplant
- ⏳ **Buchhaltung** - Geplant
- ⏳ **Dokumente** - Geplant

### Marketplace-Module (verfügbar)
- Zeiterfassung (1€/Monat)
- Rechnungen (1€/Monat)
- Kundenverwaltung (1€/Monat)
- Projektverwaltung (1€/Monat)
- Dokumentenverwaltung (1€/Monat)
- Kalender & Termine (1€/Monat)

## 🧪 Demo-Zugang

Nach dem Setup:

- **URL**: http://localhost/sys-experts-toolbox/public/
- **E-Mail**: admin@sys-experts.de
- **Passwort**: admin123
- **Rolle**: Administrator (voller Zugriff)

### Test-Szenarien

1. **Als Admin**: Alle Module sichtbar, Lizenzen verwalten
2. **Neuen User anlegen**: Benutzerverwaltung → Neuer Benutzer
3. **Lizenzen erteilen**: User auswählen → Lizenzen verwalten
4. **Rechnung erstellen**: Rechnungen → Neue Rechnung
5. **Zeit erfassen**: Zeiterfassung → Arbeit starten

## 🔧 Nützliche Befehle

```bash
# Dependencies installieren
composer install

# Datenbank komplett neu aufsetzen
php setup_database.php
php database/create_test_user.php
php database/seed_marketplace_modules.php

# Einzelne Tabellen erstellen
php create_invoice_tables.php
php create_customers_table.php
php create_settings_table.php

# Testdaten generieren
php database/seed_time_tracking.php

# Tabellen-Struktur prüfen
php database/check_table.php
```

## 📝 Entwicklung

### Neues Modul hinzufügen

1. **Controller erstellen** in `core/ModuleName/`
   ```php
   class ModuleController {
       public function index(Request $request, Response $response) { ... }
   }
   ```

2. **Tabellen erstellen** via Setup-Script
   ```php
   // create_module_tables.php
   $pdo->exec("CREATE TABLE IF NOT EXISTS bm_module_data ...");
   ```

3. **Routes registrieren** in `routes/web.php`
   ```php
   $app->group('/module', function (RouteCollectorProxy $group) { ... });
   ```

4. **Views erstellen** in `resources/views/module/`
   ```php
   // index.php, show.php, etc.
   ```

5. **Modul im Marketplace registrieren**
   ```sql
   INSERT INTO bm_modules (code, name, description, category, price_per_user)
   VALUES ('module', 'Modul-Name', 'Beschreibung', 'Kategorie', 1.00);
   ```

### Code-Konventionen

- **PSR-12** Code Style
- **Namespace**: `SysExperts\BusinessManager\ModuleName`
- **Views**: PHP-Templates mit Output-Escaping
- **Datenbank**: PDO mit Prepared Statements
- **Auth**: SessionManager + AuthService verwenden

## 🔒 Sicherheit

### Implementiert
- ✅ Passwörter mit `password_hash()` (BCRYPT)
- ✅ SQL-Injection-Schutz via Prepared Statements
- ✅ XSS-Schutz via `htmlspecialchars()`
- ✅ Session-basierte Authentifizierung
- ✅ Lizenz-basierte Zugriffskontrolle

### Geplant
- ⏳ CSRF-Protection
- ⏳ 2FA (TOTP)
- ⏳ IP-Whitelist
- ⏳ Rate-Limiting
- ⏳ Audit-Logging für alle Änderungen

## 🗺️ Roadmap

### Phase 1: Core-System (✅ Abgeschlossen)
- ✅ Auth-System
- ✅ Benutzerverwaltung
- ✅ Lizenzverwaltung
- ✅ Marketplace
- ✅ Dashboard

### Phase 2: Business-Module (🚧 In Arbeit)
- ✅ Rechnungen (Basis)
- ✅ Kunden (Basis)
- ✅ Zeiterfassung (Basis)
- ⏳ E-Mail-Versand
- ⏳ PDF-Vorlagen
- ⏳ GoBD-Konformität

### Phase 3: Multi-Tenancy (⏳ Geplant)
- ⏳ Tenant-Context in allen Queries
- ⏳ Mandanten-Registrierung
- ⏳ Setup-Assistent

### Phase 4: Partner-Konsole (⏳ Geplant)
- ⏳ Zentrale Übersicht
- ⏳ Lizenzmanagement
- ⏳ Monitoring

### Phase 5: Update-System (⏳ Geplant)
- ⏳ Auto-Updater
- ⏳ Versionsverwaltung
- ⏳ Rollback-Mechanismus

## 📄 Lizenz

Proprietär - sys-experts.de

## 🤝 Support

- **E-Mail**: support@sys-experts.de
- **Website**: https://sys-experts.de
- **Dokumentation**: Siehe `AGENTS.md` und `PROGRESS.md`

---

**Version**: 0.2.0 (Alpha)  
**Stand**: 26.10.2025  
**Status**: Development - Nicht produktionsreif
