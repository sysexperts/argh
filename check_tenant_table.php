<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');

echo "=== bm_tenants Tabelle ===\n\n";

$result = $pdo->query("PRAGMA table_info(bm_tenants)");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

if (empty($columns)) {
    echo "Tabelle existiert nicht!\n";
} else {
    echo "Spalten:\n";
    foreach ($columns as $col) {
        echo "  - {$col['name']} ({$col['type']})\n";
    }
}
