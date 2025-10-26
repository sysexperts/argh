<?php
/**
 * E-Mail-Test
 */

require __DIR__ . '/vendor/autoload.php';

use SysExperts\BusinessManager\Mail\MailService;

$config = require __DIR__ . '/config/mail.php';

// Test-Modus aktivieren
$config['test_mode'] = true;

$mailService = new MailService($config);

echo "=== E-Mail-Test (Test-Modus) ===\n\n";
echo "Von: {$config['from']['address']}\n";
echo "An: test@example.com\n\n";

$success = $mailService->send(
    'test@example.com',
    'Test-Rechnung',
    '<h1>Test-E-Mail</h1><p>Dies ist eine Test-E-Mail vom Business Manager.</p>',
    []
);

if ($success) {
    echo "✓ E-Mail erfolgreich geloggt!\n";
    echo "Siehe: storage/logs/mail.log\n";
} else {
    echo "✗ Fehler beim E-Mail-Versand\n";
}
