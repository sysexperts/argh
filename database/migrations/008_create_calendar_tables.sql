-- Kalender/Events-System

-- Events-Tabelle
CREATE TABLE IF NOT EXISTS bm_calendar_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    location VARCHAR(255) NULL,
    
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    is_all_day BOOLEAN DEFAULT 0,
    
    category VARCHAR(100) NULL,
    color VARCHAR(7) DEFAULT '#14b8a6',
    
    status VARCHAR(50) DEFAULT 'planned',
    
    -- Wiederkehrende Events
    is_recurring BOOLEAN DEFAULT 0,
    recurrence_rule TEXT NULL,
    recurrence_end_date DATETIME NULL,
    parent_event_id INTEGER NULL,
    
    -- Verknüpfungen
    customer_id INTEGER NULL,
    created_by INTEGER NOT NULL,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES bm_customers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_event_id) REFERENCES bm_calendar_events(id) ON DELETE CASCADE
);

CREATE INDEX idx_calendar_events_tenant ON bm_calendar_events(tenant_id);
CREATE INDEX idx_calendar_events_start ON bm_calendar_events(start_datetime);
CREATE INDEX idx_calendar_events_end ON bm_calendar_events(end_datetime);
CREATE INDEX idx_calendar_events_category ON bm_calendar_events(category);
CREATE INDEX idx_calendar_events_customer ON bm_calendar_events(customer_id);

-- Event-Teilnehmer
CREATE TABLE IF NOT EXISTS bm_calendar_attendees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    
    status VARCHAR(50) DEFAULT 'pending',
    response_at DATETIME NULL,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (event_id) REFERENCES bm_calendar_events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE(event_id, user_id)
);

CREATE INDEX idx_calendar_attendees_event ON bm_calendar_attendees(event_id);
CREATE INDEX idx_calendar_attendees_user ON bm_calendar_attendees(user_id);

-- Event-Erinnerungen
CREATE TABLE IF NOT EXISTS bm_calendar_reminders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    
    minutes_before INTEGER NOT NULL,
    sent_at DATETIME NULL,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (event_id) REFERENCES bm_calendar_events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_calendar_reminders_event ON bm_calendar_reminders(event_id);
CREATE INDEX idx_calendar_reminders_user ON bm_calendar_reminders(user_id);
CREATE INDEX idx_calendar_reminders_sent ON bm_calendar_reminders(sent_at);
