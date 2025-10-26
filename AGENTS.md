# 🧠 Memory Bank v0.2
**Projekt:** Mandantenfähige modulare Business-Software  
**Unternehmen:** sys-experts.de  
**Stand:** 26.10.2025  

---

## 1. Betriebs- / Geschäftsmodell
- Jede Firma (Mandant) bekommt eine eigene Installation auf ihrem eigenen Webserver (z. B. Strato).
- Gleiche Codebasis bei allen Kunden, aber pro Mandant:
  - eigene Instanz
  - eigene Datenbank
  - eigenes Branding.
- Module sind pro Benutzer lizenziert und pro Mandant verwaltet.

- Es gibt eine zentrale **Partner Console** (nur für sys-experts.de):
  - Übersicht aller Mandanten (Name, Domain, Module, Laufzeit, Status).
  - Lizenzmanagement (Module freischalten/sperren, Testfreigaben).
  - Manuelle Lizenzvergabe für einzelne Kunden.
  - Changelog / Versionen je Kunde.
  - (später) Monitoring / Heartbeat von Kundeninstanzen.

- Mandanten können sich selbst registrieren/bestellen:
  - Auswahl der gewünschten Module und User-Anzahl bei Bestellung.
  - Bestellung löst Setup-Prozess bei sys-experts aus, kein automatisches Self-Deployment beim Kunden.
  - Keine kostenlose Testversion, aber es existiert/entsteht eine Demo-Instanz mit Demo-Daten.

- Abrechnung:
  - 1 € pro Modul pro Benutzer pro Monat.
  - Rechnung an den Mandanten enthält z. B.:
    - „4× Zeiterfassung“
    - „8× Rechnungen“
  - Serverkosten sind separat, nicht Teil der Modullizenz.
  - Abrechnung ist mandantenbasiert, nicht personenbezogen.

- White-Label:
  - Kunde darf Logo + Farben + Produktnamen ändern.
  - Kunde darf NICHT das System weiterverkaufen / weitervermieten.

---

## 2. Mandanten-Architektur / Deployment
- Jeder Mandant = eigene Datenbank. Kein Shared-Tenant-Modell.
- Setup soll langfristig einen Einrichtungs-Assistenten haben:
  - DB-Host, DB-Nutzer, Passwort, DB-Name etc. werden manuell eingetragen.
- Mandant kann eigene Domain/Subdomain nutzen (z. B. subdomain.kunde.de oder kunde.sys-experts.de).
- Die Software läuft auf dem Webserver des Kunden → normaler Betrieb ist „online“.
- Lizenzprüfung darf gegen zentrale sys-experts-Konsole laufen (Heartbeat/API), das ist erlaubt.
- „Offline weiterlaufen“ ist kein wichtiges Ziel, weil die Instanz sowieso online läuft.

---

## 3. Benutzer / Rollen / Rechte
- Ein Mandant hat mehrere Benutzer (z. B. Admin, Buchhaltung, Mitarbeiter).
- Rechte sind modular:
  - Zugriff auf Modul überhaupt? (ja/nein)
  - Interne Berechtigungen innerhalb eines Moduls (lesen / ändern / administrieren).
- Gast-/Externe Benutzer sind erlaubt:
  - z. B. Kunde kann Rechnung ansehen oder Ticketstatus verfolgen.
- Benutzer gehören immer genau einem Mandanten. Kein Cross-Access.
- Benutzerverwaltung: aktuell sowohl Mandanten-Admin als auch sys-experts zentral.

---

## 4. Login / Authentifizierung
- Login-System wird neu gebaut.
- Start: klassischer Login per E-Mail + Passwort.
- Registrierung:
  - Admin (sys-experts) kann einen Mandanten anlegen.
  - Mandant kann sich „selbst registrieren“ über Bestell-Flow.
- Registrierung braucht Double Opt-In (E-Mail-Bestätigung).
- Login findet in der Kundeninstanz statt, keine zentrale globale Login-URL.
- Später geplant:
  - OAuth / SSO: Google → später Microsoft & Apple
  - 2FA (TOTP / E-Mail-Code), optional
- Zugriff soll optional via IP-Whitelist einschränkbar sein.

---

## 5. Module / Lizenzen / Marketplace
- Module sind kleinteilig gedacht:
  - „Rechnungen“ ist ein eigenes Modul.
  - „Buchhaltung“ ist ein eigenes Modul.
  - Wenn mehrere Themen eng zusammengehören, können sie unter einem Modul mit Tabs laufen.
  - Core-Funktionen sind NIE deaktivierbar: Login, Dashboard, Userverwaltung, Benachrichtigungen, Mandanten-Einstellungen.
- Jedes Modul kann pro Benutzer aktiviert sein oder nicht.
- Nicht lizenzierte Module:
  - Im Menü unsichtbar.
  - Im Marketplace sichtbar (für Admins mit Beschreibung + Preis + Buchung).
- Ablauf bei Lizenzablauf:
  - Modul verschwindet aus UI.
  - Direkter Aufruf zeigt „Keine Berechtigung / Nicht lizenziert. → Zum Marketplace“.
- Lizenzmodell:
  - 1 € pro Benutzer pro Modul / Monat.
  - Abo-basiert (monatlich).
  - Lizenzstruktur: zentral pro Mandant oder einzeln pro Modul (noch offen).

---

## 6. Updates / Versionierung / Support
- Update-System erforderlich.
- Kunden entscheiden selbst über Funktionsupdates.
- Sicherheitsupdates sind verpflichtend.
- Kunden, die Sicherheitsupdates verweigern → nach mehrfacher Mahnung kündbar.
- Anforderungen:
  - Version pro Modul pro Mandant.
  - Changelog / Release-Kanal pro Kunde.
  - Mechanismus zum Update auf fremden Webservern (Auto-Updater, kein FTP).
- E-Mail-Benachrichtigungen über neue Versionen.

---

## 7. Rechtliches / Compliance / Logging
- System muss GoBD-tauglich / revisionssicher sein.
- Rechnungen müssen nach Freigabe unveränderbar sein.
- Audit-Logs Pflicht:
  - „Benutzer A hat am 26.10.2025 Rechnung #102 geändert.“
- Logs müssen mandantenbasiert, unveränderbar und revisionssicher gespeichert werden.

---

## 8. UI / UX / Produktgefühl
- Einheitliches Layout für alle Module.
- Sidebar-Navigation für 30+ Module (collapsible, gruppierbar).
- Dashboard:
  - Widget-/Card-basiert, zeigt Module-spezifische KPIs.
- Benachrichtigungs-Center.
- Marketplace für Module:
  - Admin: buchen.
  - User: nur ansehen.
- Dark Mode ab Start.
- White-Label:
  - Kunde kann Logo, Farben, Namen ändern.
- Mehrsprachigkeit:
  - Start: Deutsch.
  - Danach: Englisch, Türkisch.

---

## 9. Sicherheit / Zugriff
- 2FA geplant (optional).
- IP-Whitelist pro Modul oder Rolle.
- Zentraler Zugriff durch sys-experts erlaubt, muss aber datenschutzkonform geprüft werden.
