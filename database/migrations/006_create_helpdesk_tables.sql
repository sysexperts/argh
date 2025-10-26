-- Helpdesk/Ticketing-System Tabellen

-- Tickets-Tabelle
CREATE TABLE IF NOT EXISTS bm_tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    ticket_number VARCHAR(50) NOT NULL UNIQUE,
    customer_id INTEGER NULL,
    assigned_to INTEGER NULL,
    created_by INTEGER NOT NULL,
    
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    
    status VARCHAR(50) DEFAULT 'open',
    priority VARCHAR(50) DEFAULT 'normal',
    category VARCHAR(100) NULL,
    
    due_date DATETIME NULL,
    resolved_at DATETIME NULL,
    closed_at DATETIME NULL,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES bm_customers(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_tickets_tenant ON bm_tickets(tenant_id);
CREATE INDEX idx_tickets_status ON bm_tickets(status);
CREATE INDEX idx_tickets_priority ON bm_tickets(priority);
CREATE INDEX idx_tickets_assigned ON bm_tickets(assigned_to);
CREATE INDEX idx_tickets_customer ON bm_tickets(customer_id);

-- Ticket-Kommentare/Antworten
CREATE TABLE IF NOT EXISTS bm_ticket_comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticket_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    
    comment TEXT NOT NULL,
    is_internal BOOLEAN DEFAULT 0,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (ticket_id) REFERENCES bm_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_ticket_comments_ticket ON bm_ticket_comments(ticket_id);

-- Ticket-Attachments
CREATE TABLE IF NOT EXISTS bm_ticket_attachments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticket_id INTEGER NOT NULL,
    comment_id INTEGER NULL,
    
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    filesize INTEGER NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    
    uploaded_by INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (ticket_id) REFERENCES bm_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES bm_ticket_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_ticket_attachments_ticket ON bm_ticket_attachments(ticket_id);

-- Ticket-History/Audit-Log
CREATE TABLE IF NOT EXISTS bm_ticket_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticket_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    
    action VARCHAR(100) NOT NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (ticket_id) REFERENCES bm_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_ticket_history_ticket ON bm_ticket_history(ticket_id);
