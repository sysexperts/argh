<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');

echo "=== users Tabelle ===\n";
$users = $pdo->query("SELECT * FROM users LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
if (count($users) > 0) {
    print_r($users[0]);
} else {
    echo "Keine Daten\n";
}

echo "\n=== bm_users Tabelle ===\n";
$bmUsers = $pdo->query("SELECT * FROM bm_users LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
if (count($bmUsers) > 0) {
    print_r($bmUsers[0]);
} else {
    echo "Keine Daten\n";
}
