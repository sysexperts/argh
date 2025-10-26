<?php
/**
 * E-Mail-Test (ECHT)
 */

require __DIR__ . '/vendor/autoload.php';

use SysExperts\BusinessManager\Mail\MailService;

$config = require __DIR__ . '/config/mail.php';

// Test-Modus DEAKTIVIEREN für echten Versand
$config['test_mode'] = false;

$mailService = new MailService($config);

echo "=== E-Mail-Test (ECHTER VERSAND) ===\n\n";
echo "SMTP: {$config['smtp']['host']}:{$config['smtp']['port']}\n";
echo "Von: {$config['from']['address']}\n";
echo "An: info@sys-experts.de\n\n";

try {
    $success = $mailService->send(
        'info@sys-experts.de',
        'Test: Business Manager E-Mail-System',
        '<html>
        <body style="font-family: Arial, sans-serif; padding: 20px;">
            <h1 style="color: #14b8a6;">✅ E-Mail-System funktioniert!</h1>
            <p>Dies ist eine Test-E-Mail vom Business Manager.</p>
            <p><strong>Konfiguration:</strong></p>
            <ul>
                <li>SMTP: smtp.office365.com:587</li>
                <li>Absender: info@sys-experts.de</li>
                <li>Verschlüsselung: TLS</li>
            </ul>
            <p>Das E-Mail-System ist einsatzbereit! 🚀</p>
        </body>
        </html>',
        []
    );

    if ($success) {
        echo "✓ E-Mail erfolgreich versendet!\n";
        echo "Prüfe dein Postfach: info@sys-experts.de\n";
    } else {
        echo "✗ Fehler beim E-Mail-Versand\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}
