-- Füge icon und url zu bm_modules hinzu

ALTER TABLE bm_modules ADD COLUMN icon VARCHAR(50) NULL;
ALTER TABLE bm_modules ADD COLUMN url VARCHAR(255) NULL;

-- Update bestehende Module mit Icons und URLs
UPDATE bm_modules SET icon = '🔐', url = '/auth/login' WHERE code = 'auth';
UPDATE bm_modules SET icon = '📊', url = '/dashboard' WHERE code = 'dashboard';
UPDATE bm_modules SET icon = '👥', url = '/users' WHERE code = 'users';
UPDATE bm_modules SET icon = '🔔', url = '/notifications' WHERE code = 'notifications';
UPDATE bm_modules SET icon = '⚙️', url = '/settings' WHERE code = 'settings';
UPDATE bm_modules SET icon = '⏱️', url = '/time-tracking' WHERE code = 'time-tracking';
UPDATE bm_modules SET icon = '🧾', url = '/invoices' WHERE code = 'invoices';
UPDATE bm_modules SET icon = '👤', url = '/customers' WHERE code = 'customers';
UPDATE bm_modules SET icon = '📁', url = '/projects' WHERE code = 'projects';
UPDATE bm_modules SET icon = '📄', url = '/documents' WHERE code = 'documents';
UPDATE bm_modules SET icon = '🎫', url = '/helpdesk' WHERE code = 'helpdesk';
UPDATE bm_modules SET icon = '📅', url = '/calendar' WHERE code = 'calendar';
