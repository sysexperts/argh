<?php
$pdo = new PDO('sqlite:database/business_manager.sqlite');
$pdo->exec("
CREATE TABLE IF NOT EXISTS bm_customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER,
    customer_number VARCHAR(50) UNIQUE,
    company_name VARCHAR(255),
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    website VARCHAR(255),
    tax_id VARCHAR(50),
    vat_id VARCHAR(50),
    street VARCHAR(255),
    zip VARCHAR(20),
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Deutschland',
    notes TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");
echo "✅ Customers table created successfully!\n";
