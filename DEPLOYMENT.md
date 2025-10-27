# 🚀 Business Manager - Deployment Guide

## Produktiv-Installation auf Kundenserver

### Voraussetzungen

**Server-Anforderungen:**
- PHP >= 8.0
- SQLite3 oder MySQL/MariaDB
- Apache oder Nginx Webserver
- SSL-Zertifikat (empfohlen)
- Mindestens 256 MB RAM
- 500 MB freier Speicherplatz

**PHP-Extensions:**
- pdo
- pdo_sqlite (oder pdo_mysql)
- json
- mbstring
- openssl

---

## 📦 Schritt 1: Dateien hochladen

### Via FTP/SFTP:
```bash
# Gesamtes Projekt hochladen
# WICHTIG: .git Ordner NICHT hochladen!
```

### Via Git (empfohlen):
```bash
cd /var/www/html  # oder dein Webroot
git clone https://github.com/sysexperts/argh.git business-manager
cd business-manager
composer install --no-dev --optimize-autoloader
```

---

## 🔧 Schritt 2: Webserver konfigurieren

### Apache (.htaccess bereits vorhanden)

**VirtualHost Beispiel:**
```apache
<VirtualHost *:80>
    ServerName kunde.domain.de
    DocumentRoot /var/www/html/business-manager/public
    
    <Directory /var/www/html/business-manager/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/business-manager-error.log
    CustomLog ${APACHE_LOG_DIR}/business-manager-access.log combined
</VirtualHost>
```

### Nginx

**nginx.conf Beispiel:**
```nginx
server {
    listen 80;
    server_name kunde.domain.de;
    root /var/www/html/business-manager/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🎯 Schritt 3: Installation durchführen

### Automatische Installation (empfohlen):

1. **Browser öffnen:**
   ```
   https://kunde.domain.de/install.php
   ```

2. **Installations-Wizard durchlaufen:**
   - ✅ Server-Anforderungen prüfen
   - ✅ Datenbank konfigurieren
   - ✅ Schema erstellen
   - ✅ Admin-User anlegen
   - ✅ Fertig!

3. **install.php löschen:**
   ```bash
   rm public/install.php
   ```

### Manuelle Installation:

```bash
# 1. .env Datei erstellen
cp .env.example .env
nano .env  # Anpassen

# 2. Datenbank-Migrationen ausführen
php database/migrate.php

# 3. Admin-User erstellen
php database/create_admin.php
```

---

## 🔐 Schritt 4: Sicherheit

### Dateiberechtigungen setzen:
```bash
# Besitzer setzen
chown -R www-data:www-data /var/www/html/business-manager

# Rechte setzen
find /var/www/html/business-manager -type d -exec chmod 755 {} \;
find /var/www/html/business-manager -type f -exec chmod 644 {} \;

# Schreibrechte für bestimmte Ordner
chmod -R 775 /var/www/html/business-manager/database
chmod -R 775 /var/www/html/business-manager/storage
```

### SSL-Zertifikat (Let's Encrypt):
```bash
# Certbot installieren
apt-get install certbot python3-certbot-apache

# Zertifikat erstellen
certbot --apache -d kunde.domain.de
```

### .env Datei schützen:
```bash
chmod 600 .env
```

---

## 📊 Schritt 5: Datenbank-Backup einrichten

### Automatisches Backup (Cron):
```bash
# Crontab bearbeiten
crontab -e

# Tägliches Backup um 2 Uhr nachts
0 2 * * * /usr/bin/sqlite3 /var/www/html/business-manager/database/business_manager.sqlite ".backup '/backup/business_manager_$(date +\%Y\%m\%d).sqlite'"
```

---

## 🔄 Updates durchführen

### Via Git:
```bash
cd /var/www/html/business-manager
git pull origin master
composer install --no-dev
php database/migrate.php  # Falls neue Migrationen
```

### Via FTP:
1. Backup erstellen
2. Neue Dateien hochladen
3. Migrationen ausführen

---

## 🐛 Troubleshooting

### Problem: 500 Internal Server Error
```bash
# Logs prüfen
tail -f /var/log/apache2/error.log

# PHP-Fehler aktivieren (nur für Debugging!)
# In .env: APP_DEBUG=true
```

### Problem: Datenbank-Verbindung fehlgeschlagen
```bash
# Rechte prüfen
ls -la database/
chmod 664 database/business_manager.sqlite
```

### Problem: Weiße Seite
```bash
# Composer-Abhängigkeiten neu installieren
composer install --no-dev
```

---

## ✅ Checkliste für Produktiv-Start

- [ ] Server-Anforderungen erfüllt
- [ ] Dateien hochgeladen
- [ ] Webserver konfiguriert
- [ ] SSL-Zertifikat installiert
- [ ] Installation durchgeführt
- [ ] install.php gelöscht
- [ ] Dateiberechtigungen gesetzt
- [ ] .env Datei geschützt
- [ ] Backup eingerichtet
- [ ] Admin-Login getestet
- [ ] Alle Module getestet
- [ ] Fehler-Logs geprüft

---

## 📞 Support

Bei Problemen:
- **E-Mail:** support@sys-experts.de
- **Dokumentation:** https://docs.sys-experts.de
- **GitHub Issues:** https://github.com/sysexperts/argh/issues

---

## 🎉 Fertig!

Dein Business Manager ist jetzt produktiv und einsatzbereit!

**Nächste Schritte:**
1. Module im Marketplace aktivieren
2. Benutzer anlegen
3. Kunden importieren
4. Erste Rechnung erstellen
