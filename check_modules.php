<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');

echo "=== Module im Marketplace ===\n\n";

$modules = $pdo->query("
    SELECT code, name, category, price_per_user, is_active, display_order 
    FROM bm_modules 
    ORDER BY display_order
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($modules as $module) {
    $status = $module['is_active'] ? '✓' : '✗';
    echo "$status {$module['name']} ({$module['code']})\n";
    echo "   Kategorie: {$module['category']}\n";
    echo "   Preis: €{$module['price_per_user']}/User/Monat\n";
    echo "   Order: {$module['display_order']}\n\n";
}

echo "Gesamt: " . count($modules) . " Module\n";
