<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');
$pdo->exec("ALTER TABLE bm_invoice_items ADD COLUMN unit VARCHAR(50) DEFAULT 'Stück'");
echo "✅ Unit column added to invoice_items!\n";
