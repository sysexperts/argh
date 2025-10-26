-- Zeiterfassungs-Tabellen (SQLite)

-- Arbeitszeiteinträge
CREATE TABLE IF NOT EXISTS time_entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    tenant_id INTEGER NOT NULL,
    date TEXT NOT NULL,
    start_time TEXT NOT NULL,
    end_time TEXT NULL,
    total_hours REAL NULL,
    overtime_hours REAL DEFAULT 0,
    status TEXT DEFAULT 'active' CHECK(status IN ('active', 'paused', 'completed')),
    notes TEXT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_time_entries_user ON time_entries(user_id);
CREATE INDEX IF NOT EXISTS idx_time_entries_tenant ON time_entries(tenant_id);
CREATE INDEX IF NOT EXISTS idx_time_entries_date ON time_entries(date);
CREATE INDEX IF NOT EXISTS idx_time_entries_status ON time_entries(status);

-- Pausen
CREATE TABLE IF NOT EXISTS time_breaks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    time_entry_id INTEGER NOT NULL,
    start_time TEXT NOT NULL,
    end_time TEXT NULL,
    duration_minutes INTEGER NULL,
    break_type TEXT DEFAULT 'regular' CHECK(break_type IN ('regular', 'lunch', 'other')),
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (time_entry_id) REFERENCES time_entries(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_time_breaks_entry ON time_breaks(time_entry_id);

-- Audit-Log für Zeiterfassung
CREATE TABLE IF NOT EXISTS time_audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    time_entry_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    action TEXT NOT NULL,
    old_values TEXT NULL,
    new_values TEXT NULL,
    ip_address TEXT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (time_entry_id) REFERENCES time_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_time_audit_entry ON time_audit_log(time_entry_id);
CREATE INDEX IF NOT EXISTS idx_time_audit_user ON time_audit_log(user_id);
CREATE INDEX IF NOT EXISTS idx_time_audit_created ON time_audit_log(created_at);
