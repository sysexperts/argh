# XAMPP Konfiguration für Business Manager

## Problem: Port 80 ist belegt

Port 80 wird bereits von einem anderen Prozess verwendet.

## Lösung: XAMPP auf Port 8080 konfigurieren

### Schritt 1: Apache Port ändern
1. Öffne: `C:\xampp\apache\conf\httpd.conf`
2. Suche: `Listen 80`
3. Ändere zu: `Listen 8080`
4. Suche: `ServerName localhost:80`
5. Ändere zu: `ServerName localhost:8080`

### Schritt 2: SSL Port ändern (optional)
1. Öffne: `C:\xampp\apache\conf\extra\httpd-ssl.conf`
2. Suche: `Listen 443`
3. Ändere zu: `Listen 4433`
4. Suche: `<VirtualHost _default_:443>`
5. Ändere zu: `<VirtualHost _default_:4433>`

### Schritt 3: Apache starten
1. Öffne XAMPP Control Panel
2. Klicke auf "Start" bei Apache
3. Öffne im Browser: `http://localhost:8080/sys-experts-toolbox/public/`

## Alternative: PHP Development Server verwenden

Wenn XAMPP zu kompliziert ist, kannst du auch den PHP Dev Server verwenden:

```bash
php -S localhost:8000 -t public
```

Dann öffne: `http://localhost:8000`

## mod_rewrite ist bereits aktiviert!

Die Datei `enable_rewrite.ps1` hat mod_rewrite bereits aktiviert.
