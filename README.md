# 🚀 Business Manager

Modulare mandantenfähige Business-Software von **sys-experts.de**

## 📋 Übersicht

Business Manager ist eine flexible, modulare Business-Software mit folgenden Kernmerkmalen:

- **Multi-Tenant**: Jeder Mandant erhält eigene Installation + eigene Datenbank
- **Modular**: Kleinteilige Module (Rechnungen, Buchhaltung, Zeiterfassung, etc.)
- **Lizenzbasiert**: 1€ pro Modul pro Benutzer pro Monat
- **White-Label**: Logo, Farben, Produktname anpassbar
- **GoBD-konform**: Revisionssichere Audit-Logs
- **Update-System**: Automatische Updates mit Versionskontrolle

## 🛠️ Tech-Stack

- **PHP 8.1+**
- **Slim Framework 4** (Routing, Middleware)
- **Phinx** (Database Migrations)
- **SQLite** (Start) → MySQL/PostgreSQL (Produktion)
- **Composer** (Dependency Management)

## 📦 Installation

### Voraussetzungen

- PHP 8.1 oder höher
- Composer
- SQLite (oder MySQL/PostgreSQL)
- Apache/Nginx mit mod_rewrite

### Setup

1. **Dependencies installieren**
   ```bash
   composer install
   ```

2. **Umgebungsvariablen konfigurieren**
   ```bash
   copy .env.example .env
   ```
   
   Passe `.env` an (DB-Verbindung, Lizenz-API, etc.)

3. **Datenbank-Migrationen ausführen**
   ```bash
   composer migrate
   ```

4. **Initiale Daten einfügen (optional)**
   ```bash
   vendor/bin/phinx seed:run
   ```

5. **Webserver konfigurieren**
   - DocumentRoot auf `public/` setzen
   - mod_rewrite aktivieren

6. **Im Browser öffnen**
   ```
   http://localhost/
   ```

## 📁 Projektstruktur

```
sys-experts-toolbox/
├── bootstrap/          # App-Bootstrap
├── config/             # Konfigurationsdateien
├── core/               # Core-System (Auth, Database, License, etc.)
├── database/           # Migrationen & Seeds
├── modules/            # Optionale Module
├── public/             # Webroot (index.php, assets)
├── routes/             # Route-Definitionen
├── storage/            # Logs, Cache, Uploads
├── tests/              # Unit/Integration Tests
├── .env.example        # Umgebungsvariablen Template
├── composer.json       # PHP Dependencies
├── phinx.php           # Migrations-Konfiguration
└── README.md
```

## 🔑 Core-Module (immer aktiv)

- **Auth** - Authentifizierung & Login
- **Dashboard** - Startseite & Widgets
- **User** - Benutzerverwaltung
- **Notification** - Benachrichtigungssystem
- **Tenant** - Mandanten-Einstellungen
- **License** - Lizenzverwaltung
- **Audit** - GoBD-konforme Audit-Logs
- **Update** - Update-System

## 📦 Optionale Module (lizenzpflichtig)

- **Invoices** - Rechnungserstellung
- **Accounting** - Buchhaltung
- **Timetracking** - Zeiterfassung
- **CRM** - Kundenmanagement
- **Projects** - Projektmanagement

## 🧪 Demo-Zugang

Nach dem Seed (`phinx seed:run`):

- **URL**: http://localhost/
- **E-Mail**: admin@demo.sys-experts.de
- **Passwort**: admin123

## 🔧 Composer-Befehle

```bash
# Dependencies installieren
composer install

# Migrationen ausführen
composer migrate

# Migration zurückrollen
composer migrate:rollback

# Neue Migration erstellen
composer migrate:create MigrationName

# Tests ausführen
composer test
```

## 📝 Entwicklung

### Neue Migration erstellen

```bash
composer migrate:create CreateTableName
```

### Datenbank-Schema aktualisieren

```bash
composer migrate
```

### Neue Module hinzufügen

1. Ordner in `modules/` erstellen (z.B. `modules/invoices/`)
2. Modul in `config/modules.php` registrieren
3. Migrations für Modul-Tabellen erstellen
4. Controller, Models, Views implementieren

## 🔒 Sicherheit

- Passwörter werden mit `password_hash()` (BCRYPT) gespeichert
- SQL-Injection-Schutz via Prepared Statements
- XSS-Schutz via Output-Escaping
- CSRF-Schutz (geplant)
- 2FA (geplant)

## 📄 Lizenz

Proprietär - sys-experts.de

## 🤝 Support

- **E-Mail**: support@sys-experts.de
- **Website**: https://sys-experts.de

---

**Version**: 0.1.0  
**Stand**: 26.10.2025
