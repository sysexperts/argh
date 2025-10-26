<?php
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO('sqlite:' . $config['connections']['sqlite']['database']);
$result = $pdo->query('PRAGMA table_info(bm_modules)');
foreach($result as $row) {
    echo $row['name'] . ' (' . $row['type'] . ')' . PHP_EOL;
}
