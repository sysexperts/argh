-- Fix bm_modules Tabelle
DROP TABLE IF EXISTS bm_modules;

CREATE TABLE IF NOT EXISTS bm_modules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    price_per_user DECIMAL(10,2) DEFAULT 1.00,
    is_core BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    display_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_modules_code ON bm_modules(code);
CREATE INDEX IF NOT EXISTS idx_modules_category ON bm_modules(category);
CREATE INDEX IF NOT EXISTS idx_modules_active ON bm_modules(is_active);
