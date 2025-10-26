<?php
/**
 * Seed: Initial Data
 * Fügt Core-Module und Demo-Daten ein
 */

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class InitialDataSeeder extends AbstractSeed
{
    public function run(): void
    {
        // Core-Module einfügen
        $modules = [
            ['code' => 'auth', 'name' => 'Authentifizierung', 'version' => '1.0.0', 'is_core' => 1, 'price_per_user' => 0.00, 'icon' => 'lock'],
            ['code' => 'dashboard', 'name' => 'Dashboard', 'version' => '1.0.0', 'is_core' => 1, 'price_per_user' => 0.00, 'icon' => 'home'],
            ['code' => 'user', 'name' => 'Benutzerverwaltung', 'version' => '1.0.0', 'is_core' => 1, 'price_per_user' => 0.00, 'icon' => 'users'],
            ['code' => 'notification', 'name' => 'Benachrichtigungen', 'version' => '1.0.0', 'is_core' => 1, 'price_per_user' => 0.00, 'icon' => 'bell'],
            ['code' => 'tenant', 'name' => 'Mandanten-Einstellungen', 'version' => '1.0.0', 'is_core' => 1, 'price_per_user' => 0.00, 'icon' => 'settings'],
            ['code' => 'license', 'name' => 'Lizenzverwaltung', 'version' => '1.0.0', 'is_core' => 1, 'price_per_user' => 0.00, 'icon' => 'key'],
            ['code' => 'audit', 'name' => 'Audit-Logs', 'version' => '1.0.0', 'is_core' => 1, 'price_per_user' => 0.00, 'icon' => 'file-text'],
            ['code' => 'update', 'name' => 'Update-System', 'version' => '1.0.0', 'is_core' => 1, 'price_per_user' => 0.00, 'icon' => 'download'],
        ];

        $this->table('bm_modules')->insert($modules)->saveData();

        // Optionale Module
        $optionalModules = [
            ['code' => 'invoices', 'name' => 'Rechnungen', 'description' => 'Rechnungserstellung und -verwaltung', 'version' => '1.0.0', 'is_core' => 0, 'price_per_user' => 1.00, 'icon' => 'file-text'],
            ['code' => 'accounting', 'name' => 'Buchhaltung', 'description' => 'Finanzbuchhaltung und Reporting', 'version' => '1.0.0', 'is_core' => 0, 'price_per_user' => 1.00, 'icon' => 'calculator'],
            ['code' => 'timetracking', 'name' => 'Zeiterfassung', 'description' => 'Arbeitszeiterfassung und Projektzeitbuchung', 'version' => '1.0.0', 'is_core' => 0, 'price_per_user' => 1.00, 'icon' => 'clock'],
            ['code' => 'crm', 'name' => 'CRM', 'description' => 'Kundenbeziehungsmanagement', 'version' => '1.0.0', 'is_core' => 0, 'price_per_user' => 1.00, 'icon' => 'users'],
            ['code' => 'projects', 'name' => 'Projektmanagement', 'description' => 'Projektverwaltung und -planung', 'version' => '1.0.0', 'is_core' => 0, 'price_per_user' => 1.00, 'icon' => 'folder'],
        ];

        $this->table('bm_modules')->insert($optionalModules)->saveData();

        // Demo-Mandant
        $tenant = [
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Demo GmbH',
            'domain' => 'demo.sys-experts.de',
            'primary_color' => '#667eea',
            'product_name' => 'Business Manager',
        ];

        $this->table('bm_tenants')->insert($tenant)->saveData();

        // Demo-Admin-Benutzer (Passwort: admin123)
        $user = [
            'tenant_id' => 1,
            'email' => 'admin@demo.sys-experts.de',
            'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
            'first_name' => 'Max',
            'last_name' => 'Mustermann',
            'role' => 'admin',
            'is_active' => 1,
            'email_verified_at' => date('Y-m-d H:i:s'),
        ];

        $this->table('bm_users')->insert($user)->saveData();
    }
}
