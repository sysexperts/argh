<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
echo "Vorhandene Tabellen:\n";
foreach ($tables as $table) {
    echo "- $table\n";
}
