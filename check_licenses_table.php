<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');
$result = $pdo->query('PRAGMA table_info(bm_module_licenses)');
echo "=== bm_module_licenses Struktur ===\n\n";
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo $row['name'] . ' (' . $row['type'] . ')' . PHP_EOL;
}
