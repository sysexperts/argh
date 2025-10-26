<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');
$pdo->exec("ALTER TABLE bm_invoice_items ADD COLUMN tax_rate DECIMAL(5,2) DEFAULT 19");
echo "✅ Tax rate column added to invoice_items!\n";
