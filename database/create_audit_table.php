<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/business_manager.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = file_get_contents(__DIR__ . '/migrations/012_create_audit_logs.sql');
$pdo->exec($sql);

echo "✓ Audit-Logs Tabelle erstellt\n";
