#!/usr/bin/env php
<?php
/**
 * Cron-Job: Prüfe auf Updates und sende Benachrichtigungen
 * 
 * Sollte täglich laufen:
 * 0 9 * * * /usr/bin/php /path/to/bin/cron-update-check.php
 */

require __DIR__ . '/../vendor/autoload.php';

use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Updates\UpdateNotificationService;

echo "=== Update Check Cron-Job ===\n";
echo date('Y-m-d H:i:s') . "\n\n";

// Lade Config
$config = require __DIR__ . '/../config/database.php';
$db = new Database($config);

// Prüfe auf neue Releases seit letzter Prüfung
$lastCheck = getLastCheckTime();
$newReleases = $db->fetchAll("
    SELECT * FROM bm_releases 
    WHERE created_at > ? 
    ORDER BY release_date DESC
", [$lastCheck]);

if (empty($newReleases)) {
    echo "✓ Keine neuen Releases seit letzter Prüfung\n";
} else {
    echo "📦 {count($newReleases)} neue Release(s) gefunden:\n\n";
    
    $notificationService = new UpdateNotificationService($db);
    
    foreach ($newReleases as $release) {
        echo "  - Version {$release['version']}: {$release['title']}\n";
        
        // Sende Benachrichtigungen
        try {
            $notificationService->notifyNewRelease($release);
            echo "    ✓ Benachrichtigungen versendet\n";
        } catch (\Exception $e) {
            echo "    ✗ Fehler: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
}

// Prüfe auf verpasste Sicherheitsupdates
echo "🔒 Prüfe auf verpasste Sicherheitsupdates...\n";
$notificationService = new UpdateNotificationService($db);

try {
    $notificationService->notifyMissedSecurityUpdates();
    echo "✓ Sicherheitswarnungen versendet\n";
} catch (\Exception $e) {
    echo "✗ Fehler: " . $e->getMessage() . "\n";
}

// Speichere Zeitpunkt der Prüfung
saveLastCheckTime();

echo "\n✓ Cron-Job abgeschlossen\n";

/**
 * Hole Zeitpunkt der letzten Prüfung
 */
function getLastCheckTime(): string
{
    $file = __DIR__ . '/../storage/last_update_check.txt';
    
    if (file_exists($file)) {
        return trim(file_get_contents($file));
    }
    
    // Fallback: vor 24 Stunden
    return date('Y-m-d H:i:s', strtotime('-24 hours'));
}

/**
 * Speichere Zeitpunkt der Prüfung
 */
function saveLastCheckTime(): void
{
    $file = __DIR__ . '/../storage/last_update_check.txt';
    $dir = dirname($file);
    
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    file_put_contents($file, date('Y-m-d H:i:s'));
}
