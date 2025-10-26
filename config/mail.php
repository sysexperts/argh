<?php
/**
 * E-Mail-Konfiguration
 * 
 * Unterstützt SMTP und PHP mail()
 */

return [
    // Standard-Treiber: 'smtp' oder 'mail'
    'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
    
    // SMTP-Einstellungen
    // Unterstützte Provider:
    // - Gmail: smtp.gmail.com:587 (TLS)
    // - Outlook/Hotmail: smtp-mail.outlook.com:587 (TLS)
    // - Office 365: smtp.office365.com:587 (TLS)
    // - Strato: smtp.strato.de:587 (TLS)
    // - 1&1/IONOS: smtp.ionos.de:587 (TLS)
    'smtp' => [
        'host' => $_ENV['MAIL_HOST'] ?? 'smtp.office365.com',
        'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls', // 'tls' oder 'ssl'
        'username' => $_ENV['MAIL_USERNAME'] ?? 'info@sys-experts.de',
        'password' => $_ENV['MAIL_PASSWORD'] ?? 'nthdqclhssmdslpk',
        'timeout' => 30,
    ],
    
    // Absender-Informationen
    'from' => [
        'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'info@sys-experts.de',
        'name' => $_ENV['MAIL_FROM_NAME'] ?? 'sys-experts.de',
    ],
    
    // Test-Modus (E-Mails werden nicht wirklich versendet)
    // Auf true setzen bis SMTP-Credentials korrekt sind
    'test_mode' => (bool)($_ENV['MAIL_TEST_MODE'] ?? true),
    
    // Log-Datei für Test-Modus
    'test_log' => __DIR__ . '/../storage/logs/mail.log',
];
