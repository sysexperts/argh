<?php
/**
 * Database Setup Script
 * Erstellt alle Tabellen direkt
 */

$dbPath = __DIR__ . '/database/business_manager.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating tables...\n\n";
    
    // Tenants
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bm_tenants (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            domain VARCHAR(255),
            is_active BOOLEAN DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ bm_tenants\n";
    
    // Users
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bm_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tenant_id INTEGER,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            role VARCHAR(50) DEFAULT 'user',
            is_active BOOLEAN DEFAULT 1,
            last_login_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tenant_id) REFERENCES bm_tenants(id)
        )
    ");
    echo "✓ bm_users\n";
    
    // Modules
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bm_modules (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code VARCHAR(100) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100),
            is_core BOOLEAN DEFAULT 0,
            price_per_user DECIMAL(10,2) DEFAULT 1.00,
            display_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ bm_modules\n";
    
    // Licenses
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bm_module_licenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tenant_id INTEGER,
            module_id INTEGER,
            user_id INTEGER,
            is_enabled BOOLEAN DEFAULT 1,
            expires_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tenant_id) REFERENCES bm_tenants(id),
            FOREIGN KEY (module_id) REFERENCES bm_modules(id),
            FOREIGN KEY (user_id) REFERENCES bm_users(id)
        )
    ");
    echo "✓ bm_module_licenses\n";
    
    // Audit Logs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bm_audit_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tenant_id INTEGER,
            user_id INTEGER,
            action VARCHAR(255) NOT NULL,
            entity_type VARCHAR(100),
            entity_id INTEGER,
            old_values TEXT,
            new_values TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tenant_id) REFERENCES bm_tenants(id),
            FOREIGN KEY (user_id) REFERENCES bm_users(id)
        )
    ");
    echo "✓ bm_audit_logs\n";
    
    echo "\n✅ All tables created successfully!\n\n";
    
    // Insert demo data
    echo "Inserting demo data...\n\n";
    
    // Demo Tenant
    $pdo->exec("INSERT OR IGNORE INTO bm_tenants (id, name, domain) VALUES (1, 'Demo GmbH', 'demo.sys-experts.de')");
    echo "✓ Demo Tenant\n";
    
    // Demo Modules
    $modules = [
        ['auth', 'Authentifizierung', 'Core', 1],
        ['dashboard', 'Dashboard', 'Core', 1],
        ['users', 'Benutzerverwaltung', 'Core', 1],
        ['notifications', 'Benachrichtigungen', 'Core', 1],
        ['settings', 'Einstellungen', 'Core', 1],
    ];
    
    foreach ($modules as $i => $mod) {
        $pdo->exec("INSERT OR IGNORE INTO bm_modules (code, name, category, is_core, display_order) 
                    VALUES ('{$mod[0]}', '{$mod[1]}', '{$mod[2]}', {$mod[3]}, $i)");
    }
    echo "✓ Core Modules\n";
    
    // Demo User
    $password = password_hash('admin', PASSWORD_DEFAULT);
    $pdo->exec("INSERT OR IGNORE INTO bm_users (id, tenant_id, email, password_hash, first_name, last_name, role) 
                VALUES (1, 1, 'admin@demo.sys-experts.de', '$password', 'Admin', 'User', 'admin')");
    echo "✓ Demo User (admin@demo.sys-experts.de / admin)\n";
    
    echo "\n✅ Setup complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
