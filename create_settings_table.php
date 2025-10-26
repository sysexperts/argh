<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');
$pdo->exec("
CREATE TABLE IF NOT EXISTS bm_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER DEFAULT 1,
    setting_key VARCHAR(100) UNIQUE,
    setting_value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Füge Standard-Einstellungen ein
INSERT OR IGNORE INTO bm_settings (setting_key, setting_value) VALUES
('company_name', 'Ihre Firma GmbH'),
('company_street', 'Musterstraße 123'),
('company_zip', '12345'),
('company_city', 'Musterstadt'),
('company_country', 'Deutschland'),
('company_phone', '+49 123 456789'),
('company_email', 'info@ihre-firma.de'),
('company_website', 'www.ihre-firma.de'),
('company_tax_id', 'DE123456789'),
('company_vat_id', 'DE987654321'),
('bank_name', 'Musterbank'),
('bank_iban', 'DE89 3704 0044 0532 0130 00'),
('bank_bic', 'COBADEFFXXX'),
('invoice_prefix', 'RE'),
('invoice_footer', 'Vielen Dank für Ihr Vertrauen!'),
('email_from_name', 'Ihre Firma GmbH'),
('email_from_address', 'rechnungen@ihre-firma.de');
");
echo "✅ Settings table created with default values!\n";
