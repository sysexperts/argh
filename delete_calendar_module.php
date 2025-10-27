<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');
$pdo->exec('DELETE FROM bm_modules WHERE code = "calendar"');
echo "Deleted calendar module\n";
