<?php
declare(strict_types=1);
/**
 * Navigation Service
 * 
 * @package SysExperts\BusinessManager\Navigation
 */

namespace SysExperts\BusinessManager\Navigation;

use SysExperts\BusinessManager\Database\Database;

class NavigationService
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Hole Navigation-Items für aktuellen User
     */
    public function getNavigation(?int $userId, string $currentPath = '/', ?string $userRole = null): array
    {
        // Module laden (fallback, falls Tabellen noch nicht existieren)
        try {
            $stmt = $this->db->getConnection()->prepare("
                SELECT * FROM bm_modules WHERE is_active = 1 ORDER BY sort_order ASC
            ");
            $stmt->execute();
            $modules = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Fallback: keine dynamischen Module
            $modules = [];
        }

        // Prüfe ob User Admin ist
        $isAdmin = ($userRole === 'admin');

        // Basis-Navigation für alle User
        $baseItems = [
            [
                'label' => 'Dashboard',
                'url' => '/dashboard',
                'icon' => '📊',
                'active' => $currentPath === '/dashboard',
            ],
            [
                'label' => 'Marketplace',
                'url' => '/marketplace',
                'icon' => '🛒',
                'active' => str_starts_with($currentPath, '/marketplace'),
            ],
            [
                'label' => 'Einstellungen',
                'url' => '/settings',
                'icon' => '⚙️',
                'active' => str_starts_with($currentPath, '/settings'),
            ],
        ];

        // Admin-spezifische Navigation
        $adminItems = [
            [
                'label' => 'Benutzerverwaltung',
                'url' => '/users',
                'icon' => '👥',
                'active' => str_starts_with($currentPath, '/users'),
            ],
            [
                'label' => 'Meine Module',
                'url' => '/modules',
                'icon' => '📦',
                'active' => str_starts_with($currentPath, '/modules'),
            ],
        ];

        // Lizenzierte Module aus DB laden
        $licensedModules = [];
        if ($userId) {
            try {
                $stmt = $this->db->getConnection()->prepare("
                    SELECT m.code, m.name
                    FROM bm_modules m
                    INNER JOIN bm_module_licenses ml ON m.id = ml.module_id
                    WHERE ml.user_id = ? AND ml.is_enabled = 1 AND m.is_active = 1
                    ORDER BY m.display_order, m.name
                ");
                $stmt->execute([$userId]);
                $licenses = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                foreach ($licenses as $license) {
                    $url = $this->getModuleUrlByCode($license['code']);
                    $licensedModules[] = [
                        'label' => $license['name'],
                        'url' => $url,
                        'icon' => $this->getModuleIcon($license['code']),
                        'active' => str_starts_with($currentPath, $url),
                    ];
                }
            } catch (\PDOException $e) {
                // Fallback: keine lizenzierten Module
            }
        }

        // Navigation zusammenbauen
        $items = $baseItems;
        
        if ($isAdmin) {
            // Admin sieht alles (Admin-Items + alle verfügbaren Module)
            $adminModules = [
                [
                    'label' => 'Kunden',
                    'url' => '/customers',
                    'icon' => '👥',
                    'active' => str_starts_with($currentPath, '/customers'),
                ],
                [
                    'label' => 'Rechnungen',
                    'url' => '/invoices',
                    'icon' => '🧾',
                    'active' => str_starts_with($currentPath, '/invoices'),
                ],
                [
                    'label' => 'Zeiterfassung',
                    'url' => '/time-tracking',
                    'icon' => '⏱️',
                    'active' => str_starts_with($currentPath, '/time-tracking'),
                ],
            ];
            $items = array_merge($items, $adminItems, $adminModules);
        } else {
            // Normale User sehen nur ihre lizenzierten Module
            if (!empty($licensedModules)) {
                $items = array_merge($items, $licensedModules);
            }
        }

        $navigation = [
            [
                'label' => 'Hauptmenü',
                'items' => $items,
            ],
        ];

        // Core Module (außer die, die wir manuell hinzugefügt haben)
        $skipModules = ['users', 'dashboard', 'auth', 'notifications', 'settings', 'marketplace'];
        $coreItems = [];
        foreach ($modules as $module) {
            if ($module['is_core'] && !in_array($module['code'], $skipModules)) {
                $coreItems[] = $this->moduleToNavItem($module, $currentPath);
            }
        }

        if (!empty($coreItems)) {
            $navigation[] = [
                'label' => 'System',
                'items' => $coreItems,
            ];
        }

        // Optional Module (gruppiert nach Kategorie) - DEAKTIVIERT, da wir Module manuell verwalten
        // $optionalModules = array_filter($modules, fn($m) => !$m['is_core']);
        // $grouped = $this->groupModulesByCategory($optionalModules);
        // foreach ($grouped as $category => $items) {
        //     $navigation[] = [
        //         'label' => $category,
        //         'items' => array_map(fn($m) => $this->moduleToNavItem($m, $currentPath), $items),
        //     ];
        // }

        return $navigation;
    }

    /**
     * Konvertiere Modul zu Navigation-Item
     */
    private function moduleToNavItem(array $module, string $currentPath): array
    {
        $slug = $module['code'] ?? $module['slug'] ?? 'unknown';
        $url = $this->getModuleUrl($slug);
        
        return [
            'label' => $module['name'],
            'url' => $url,
            'icon' => $this->getModuleIcon($slug),
            'active' => str_starts_with($currentPath, $url),
        ];
    }

    /**
     * Gruppiere Module nach Kategorie
     */
    private function groupModulesByCategory(array $modules): array
    {
        $grouped = [];
        
        foreach ($modules as $module) {
            $category = $module['category'] ?? 'Sonstiges';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $module;
        }

        return $grouped;
    }

    /**
     * Hole URL für Modul
     */
    private function getModuleUrl(string $slug): string
    {
        return '/modules/' . $slug;
    }

    /**
     * Hole URL für Modul anhand Code
     */
    private function getModuleUrlByCode(string $code): string
    {
        // Map module codes to actual URLs
        $urlMap = [
            'time-tracking' => '/time-tracking',
            'invoices' => '/invoices',
            'customers' => '/customers',
            'projects' => '/projects',
            'documents' => '/documents',
            'calendar' => '/calendar',
        ];
        
        return $urlMap[$code] ?? '/modules/' . $code;
    }

    /**
     * Hole Icon für Modul
     */
    private function getModuleIcon(string $slug): string
    {
        $icons = [
            'users' => '👥',
            'notifications' => '🔔',
            'settings' => '⚙️',
            'invoices' => '🧾',
            'accounting' => '💰',
            'time-tracking' => '⏱️',
            'projects' => '📁',
            'tasks' => '✅',
            'calendar' => '📅',
            'contacts' => '👤',
            'documents' => '📄',
            'reports' => '📈',
            'inventory' => '📦',
            'crm' => '🤝',
            'hr' => '👔',
            'payroll' => '💵',
            'expenses' => '💳',
            'tickets' => '🎫',
            'wiki' => '📚',
            'chat' => '💬',
        ];

        return $icons[$slug] ?? '📌';
    }
}
