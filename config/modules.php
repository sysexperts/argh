<?php
/**
 * Modul-Konfiguration
 */

return [
    // Core-Module (NIE deaktivierbar)
    'core' => [
        'auth',
        'dashboard',
        'user',
        'notification',
        'tenant',
        'license',
        'audit',
        'update',
    ],
    
    // Optionale Module (lizenzpflichtig)
    'optional' => [
        'invoices' => [
            'name' => 'Rechnungen',
            'description' => 'Rechnungserstellung und -verwaltung',
            'price' => 1.00,
            'icon' => 'file-text',
            'enabled' => false,
        ],
        'accounting' => [
            'name' => 'Buchhaltung',
            'description' => 'Finanzbuchhaltung und Reporting',
            'price' => 1.00,
            'icon' => 'calculator',
            'enabled' => false,
        ],
        'timetracking' => [
            'name' => 'Zeiterfassung',
            'description' => 'Arbeitszeiterfassung und Projektzeitbuchung',
            'price' => 1.00,
            'icon' => 'clock',
            'enabled' => false,
        ],
        'crm' => [
            'name' => 'CRM',
            'description' => 'Kundenbeziehungsmanagement',
            'price' => 1.00,
            'icon' => 'users',
            'enabled' => false,
        ],
        'projects' => [
            'name' => 'Projektmanagement',
            'description' => 'Projektverwaltung und -planung',
            'price' => 1.00,
            'icon' => 'folder',
            'enabled' => false,
        ],
    ],
];
