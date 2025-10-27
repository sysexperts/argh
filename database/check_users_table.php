<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/business_manager.sqlite');
$result = $pdo->query('PRAGMA table_info(users)');

echo "Users Tabelle Spalten:\n";
while ($row = $result->fetch()) {
    echo "- " . $row['name'] . "\n";
}
