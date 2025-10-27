<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');

echo "=== Module in bm_modules ===\n\n";

$modules = $pdo->query("
    SELECT id, code, name, icon, url, is_core 
    FROM bm_modules 
    WHERE is_active = 1
    ORDER BY display_order
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($modules as $module) {
    echo "{$module['name']} ({$module['code']})\n";
    echo "  Icon: " . ($module['icon'] ?: '❌ FEHLT') . "\n";
    echo "  URL: " . ($module['url'] ?: '❌ FEHLT') . "\n";
    echo "  Core: " . ($module['is_core'] ? 'Ja' : 'Nein') . "\n\n";
}

echo "\n=== Lizenzen für User 1 (Admin) ===\n\n";

$licenses = $pdo->query("
    SELECT m.code, m.name, ml.is_enabled
    FROM bm_module_licenses ml
    INNER JOIN bm_modules m ON ml.module_id = m.id
    WHERE ml.user_id = 1
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($licenses as $license) {
    $status = $license['is_enabled'] ? '✓' : '✗';
    echo "$status {$license['name']} ({$license['code']})\n";
}
