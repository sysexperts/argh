-- Drop alte bm_tenants Tabelle und erstelle neue

DROP TABLE IF EXISTS bm_tenants;
DROP TABLE IF EXISTS bm_tenant_licenses;
DROP TABLE IF EXISTS bm_tenant_versions;
DROP TABLE IF EXISTS bm_tenant_heartbeats;
DROP TABLE IF EXISTS bm_tenant_notes;
DROP TABLE IF EXISTS bm_releases;

-- Mandanten (Kunden-Installationen)
CREATE TABLE bm_tenants (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_name VARCHAR(255) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NOT NULL UNIQUE,
    contact_email VARCHAR(255) NOT NULL,
    contact_phone VARCHAR(50),
    tenant_status VARCHAR(50) DEFAULT 'active',
    trial_until DATE,
    logo_url VARCHAR(255),
    primary_color VARCHAR(7) DEFAULT '#14b8a6',
    product_name VARCHAR(100) DEFAULT 'Business Manager',
    server_host VARCHAR(255),
    server_ip VARCHAR(45),
    installed_version VARCHAR(20),
    last_heartbeat DATETIME,
    billing_cycle VARCHAR(20) DEFAULT 'monthly',
    next_billing_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Mandanten-Lizenzen
CREATE TABLE bm_tenant_licenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    module_id INTEGER NOT NULL,
    user_count INTEGER DEFAULT 1,
    is_active INTEGER DEFAULT 1,
    activated_at DATETIME,
    expires_at DATE,
    is_trial INTEGER DEFAULT 0,
    trial_days INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES bm_tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES bm_modules(id) ON DELETE CASCADE,
    UNIQUE(tenant_id, module_id)
);

-- Deployment Versionen
CREATE TABLE bm_tenant_versions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    module_code VARCHAR(50) NOT NULL,
    version VARCHAR(20) NOT NULL,
    deployed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    deployed_by VARCHAR(100),
    changelog TEXT,
    is_security_update INTEGER DEFAULT 0,
    FOREIGN KEY (tenant_id) REFERENCES bm_tenants(id) ON DELETE CASCADE
);

-- Heartbeat/Monitoring
CREATE TABLE bm_tenant_heartbeats (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    php_version VARCHAR(20),
    database_size INTEGER,
    user_count INTEGER,
    active_modules INTEGER,
    response_time INTEGER,
    memory_usage INTEGER,
    disk_usage INTEGER,
    heartbeat_status VARCHAR(20) DEFAULT 'online',
    error_message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES bm_tenants(id) ON DELETE CASCADE
);

-- Changelog/Release Notes
CREATE TABLE bm_releases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    version VARCHAR(20) NOT NULL UNIQUE,
    release_date DATE NOT NULL,
    release_type VARCHAR(20) DEFAULT 'feature',
    title VARCHAR(255) NOT NULL,
    description TEXT,
    changelog TEXT,
    is_security_update INTEGER DEFAULT 0,
    is_breaking_change INTEGER DEFAULT 0,
    requires_migration INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Mandanten-Notizen
CREATE TABLE bm_tenant_notes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    author VARCHAR(100) NOT NULL,
    note TEXT NOT NULL,
    note_type VARCHAR(50) DEFAULT 'general',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES bm_tenants(id) ON DELETE CASCADE
);
