-- Benachrichtigungssystem

CREATE TABLE IF NOT EXISTS bm_notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    user_id INTEGER NULL,
    
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    link VARCHAR(500) NULL,
    icon VARCHAR(50) NULL,
    
    is_read BOOLEAN DEFAULT 0,
    read_at DATETIME NULL,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_notifications_tenant ON bm_notifications(tenant_id);
CREATE INDEX idx_notifications_user ON bm_notifications(user_id);
CREATE INDEX idx_notifications_read ON bm_notifications(is_read);
CREATE INDEX idx_notifications_type ON bm_notifications(type);
