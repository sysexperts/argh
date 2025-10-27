# 🚀 Strato Webhosting - Installation

## Schnellstart für Strato-Kunden

### ⏱️ Zeitaufwand: 10-15 Minuten

---

## 📋 Was du brauchst

- ✅ Strato Webhosting-Paket (Basic oder höher)
- ✅ FTP-Zugangsdaten (aus Strato-Kundenbereich)
- ✅ Domain (bereits bei Strato eingerichtet)
- ✅ FileZilla oder anderes FTP-Programm

---

## 🎯 Schritt 1: Deployment-Paket erstellen

### Auf deinem PC:
```bash
cd c:\xampp\htdocs\sys-experts-toolbox
create-deployment-package.bat
```

Das erstellt einen Ordner: `business-manager-deploy`

### Zippen:
```
Rechtsklick auf "business-manager-deploy"
→ Senden an → ZIP-komprimierter Ordner
→ Ergibt: business-manager-deploy.zip
```

---

## 🎯 Schritt 2: Bei Strato einloggen

1. Gehe zu: https://www.strato.de/apps/CustomerService
2. Login mit deinen Strato-Zugangsdaten
3. Navigiere zu: **Webhosting** → **Verwaltung**

---

## 🎯 Schritt 3: FTP-Zugang einrichten (falls noch nicht vorhanden)

### Im Strato-Kundenbereich:
1. **Webhosting** → **FTP-Zugänge**
2. **Neuen FTP-Zugang anlegen** (falls keiner existiert)
3. Notiere:
   - FTP-Server: `ftp.strato.de` (oder deine spezifische)
   - Benutzername: z.B. `ftp123456`
   - Passwort: (selbst gewählt)

---

## 🎯 Schritt 4: Dateien hochladen

### Mit FileZilla:

1. **FileZilla öffnen**
2. **Verbindung herstellen:**
   - Host: `ftp.strato.de`
   - Benutzername: `dein-ftp-user`
   - Passwort: `dein-ftp-passwort`
   - Port: `21`
   - → **Verbinden**

3. **Struktur auf Strato:**
   ```
   Linke Seite (dein PC):
   C:\xampp\htdocs\business-manager-deploy\

   Rechte Seite (Strato):
   /
   └── (deine-domain.de)/
   ```

4. **Hochladen:**
   - Gehe rechts in den Ordner deiner Domain
   - Markiere ALLE Dateien links
   - Rechtsklick → **Hochladen**
   - ⏳ Warte bis fertig (kann 5-10 Min dauern)

### Ergebnis auf Strato:
```
/(deine-domain.de)/
├── bootstrap/
├── core/
├── database/
├── public/           ← Wichtig!
│   ├── index.php
│   ├── install.php
│   └── .htaccess
├── resources/
├── routes/
├── vendor/
├── .env.example
└── STRATO_INSTALLATION.txt
```

---

## 🎯 Schritt 5: Document Root anpassen (WICHTIG!)

### Bei Strato im Kundenbereich:

1. **Webhosting** → **Verwaltung**
2. **Domain-Einstellungen** → Deine Domain auswählen
3. **Document Root** ändern zu: `/public`
4. **Speichern**

### Falls "Document Root" nicht änderbar:

**Plan B:** Verschiebe alles aus `public/` eine Ebene höher:

```bash
# Via FTP oder SSH:
mv public/* ./
mv public/.htaccess ./
rmdir public
```

Dann in `index.php` anpassen:
```php
// Zeile 1-5 in index.php:
require __DIR__ . '/bootstrap/app.php';  // Statt ../bootstrap/app.php
```

---

## 🎯 Schritt 6: Installation durchführen

### Im Browser:

**Variante A (wenn Document Root = /public):**
```
https://deine-domain.de/install.php
```

**Variante B (wenn Document Root = /):**
```
https://deine-domain.de/public/install.php
```

### Installations-Wizard:

1. **Server-Anforderungen prüfen**
   - Sollte alles grün sein ✅
   - Falls rot: Kontaktiere Strato-Support

2. **Datenbank konfigurieren**
   - App-URL: `https://deine-domain.de`
   - → **Konfiguration speichern**

3. **Datenbank-Schema erstellen**
   - → **Datenbank-Schema erstellen**
   - ⏳ Wartet kurz...

4. **Administrator erstellen**
   - Firmenname: `Deine Firma GmbH`
   - Vorname: `Max`
   - Nachname: `Mustermann`
   - E-Mail: `admin@deine-domain.de`
   - Passwort: `SicheresPasswort123!`
   - → **Administrator erstellen**

5. **Fertig!** ✅
   - → **Zum Login**

---

## 🎯 Schritt 7: Aufräumen (WICHTIG!)

### install.php löschen:

**Via FTP:**
```
Rechte Seite (Strato):
/public/install.php
→ Rechtsklick → Löschen
```

**Oder via Strato File Manager:**
1. Webhosting → File Manager
2. Navigiere zu `/public/install.php`
3. Löschen

---

## 🎯 Schritt 8: Testen

### Login:
```
https://deine-domain.de
E-Mail: admin@deine-domain.de
Passwort: (dein gewähltes Passwort)
```

### Teste:
- ✅ Dashboard öffnet
- ✅ Sidebar funktioniert
- ✅ Module sind sichtbar
- ✅ Benutzerverwaltung funktioniert

---

## 🔒 Schritt 9: Sicherheit

### SSL-Zertifikat aktivieren (falls noch nicht):

1. **Strato-Kundenbereich**
2. **SSL-Verwaltung**
3. **Let's Encrypt aktivieren** (kostenlos!)
4. Warte 10-15 Minuten
5. Dann: `https://` statt `http://`

### .env Datei schützen:

**Via FTP:** Rechte auf `.env` setzen:
```
Rechtsklick auf .env
→ Dateiberechtigungen
→ 600 (rw-------)
```

---

## 🐛 Troubleshooting

### Problem: "500 Internal Server Error"

**Lösung 1:** Prüfe `.htaccess`
```apache
# In public/.htaccess sollte stehen:
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

**Lösung 2:** PHP-Version prüfen
- Strato-Kundenbereich → PHP-Einstellungen
- Mindestens PHP 8.0 wählen

### Problem: "Seite nicht gefunden"

**Lösung:** Document Root prüfen
- Muss auf `/public` zeigen
- Oder alle Dateien aus `public/` eine Ebene höher

### Problem: "Database not found"

**Lösung:** Schreibrechte prüfen
```
Via FTP:
/database/ → Rechte auf 775
/database/business_manager.sqlite → Rechte auf 664
```

### Problem: "Composer dependencies missing"

**Lösung:** Vendor-Ordner hochladen
- Stelle sicher, dass `/vendor` komplett hochgeladen wurde
- Oder auf Server: `composer install --no-dev`

---

## 📞 Strato-Support

Falls Probleme auftreten:

**Strato-Hotline:**
- Tel: 030 300 146 000
- Mo-Fr: 8-22 Uhr, Sa-So: 10-18 Uhr

**Häufige Fragen an Strato:**
- "Wie ändere ich den Document Root auf /public?"
- "Welche PHP-Version ist aktiv?"
- "Wie setze ich Dateiberechtigungen?"

---

## ✅ Checkliste

- [ ] Deployment-Paket erstellt
- [ ] Dateien via FTP hochgeladen
- [ ] Document Root auf /public gesetzt
- [ ] Installation durchgeführt
- [ ] install.php gelöscht
- [ ] SSL aktiviert
- [ ] Login getestet
- [ ] Alle Module getestet

---

## 🎉 Fertig!

Deine Business Manager Installation ist jetzt live unter:
```
https://deine-domain.de
```

**Nächste Schritte:**
1. Module im Marketplace aktivieren
2. Weitere Benutzer anlegen
3. Kunden importieren
4. Loslegen! 🚀

---

**Support:** support@sys-experts.de
