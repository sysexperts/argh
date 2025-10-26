<?php
/**
 * Test: Benachrichtigung für neues Modul
 */

require __DIR__ . '/vendor/autoload.php';

use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Notifications\NotificationService;

$config = require __DIR__ . '/config/database.php';
$db = new Database($config);

$notificationService = new NotificationService($db);

echo "=== Test: Marketplace-Benachrichtigung ===\n\n";

// Benachrichtigung für Tenant 1 (alle User)
$notificationService->notifyNewModule(
    1, // Tenant ID
    'Helpdesk & Ticketing',
    'Professionelles Ticketing-System für Support und Kundenservice. Verwalten Sie Tickets, weisen Sie sie Mitarbeitern zu, tracken Sie Status und Prioritäten.'
);

echo "✓ Benachrichtigung an alle User von Tenant 1 gesendet!\n";
echo "  Modul: Helpdesk & Ticketing\n";
echo "\n";

// Auch für Tenant 2
$notificationService->notifyNewModule(
    2, // Tenant ID
    'Helpdesk & Ticketing',
    'Professionelles Ticketing-System für Support und Kundenservice. Verwalten Sie Tickets, weisen Sie sie Mitarbeitern zu, tracken Sie Status und Prioritäten.'
);

echo "✓ Benachrichtigung an alle User von Tenant 2 gesendet!\n";
echo "\n";
echo "Öffne das Dashboard und klicke auf das Benachrichtigungs-Icon! 🔔\n";
