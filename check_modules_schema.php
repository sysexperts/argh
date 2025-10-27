<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');

echo "=== bm_modules Schema ===\n";
$result = $pdo->query("PRAGMA table_info(bm_modules)")->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $column) {
    echo "- {$column['name']} ({$column['type']})\n";
}

echo "\n=== Beispiel-Daten ===\n";
$modules = $pdo->query("SELECT * FROM bm_modules LIMIT 1")->fetchAll(PDO::FETCH_ASSOC);
if (count($modules) > 0) {
    print_r($modules[0]);
}
