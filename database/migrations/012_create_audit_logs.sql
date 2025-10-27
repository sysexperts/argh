-- Audit-Logs (GoBD-konform, unveränderbar)
-- Protokolliert ALLE wichtigen Aktionen im System

DROP TABLE IF EXISTS bm_audit_logs;

CREATE TABLE bm_audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    
    -- Wer?
    user_id INTEGER NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    
    -- Was?
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INTEGER,
    
    -- Details
    description TEXT NOT NULL,
    old_values TEXT,
    new_values TEXT,
    
    -- Kontext
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_method VARCHAR(10),
    request_url TEXT,
    
    -- Metadaten
    severity VARCHAR(20) DEFAULT 'info',
    category VARCHAR(50),
    
    -- Zeitstempel (unveränderbar!)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    
    -- Checksumme für Integrität
    checksum VARCHAR(64),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);

-- Index für Performance
CREATE INDEX IF NOT EXISTS idx_audit_user ON bm_audit_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_audit_entity ON bm_audit_logs(entity_type, entity_id);
CREATE INDEX IF NOT EXISTS idx_audit_action ON bm_audit_logs(action);
CREATE INDEX IF NOT EXISTS idx_audit_created ON bm_audit_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_audit_severity ON bm_audit_logs(severity);

-- WICHTIG: Keine DELETE-Berechtigung für normale User!
-- Audit-Logs dürfen NIEMALS gelöscht werden (GoBD)
