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
    public function getNavigation(?int $userId, string $currentPath = '/'): array
    {
        // Module laden (fallback, falls Tabellen noch nicht existieren)
        try {
            if (!$userId) {
                $modules = $this->db->fetchAll("
                    SELECT * FROM bm_modules
                    WHERE is_core = 1
                    ORDER BY display_order ASC, name ASC
                ");
            } else {
                $modules = $this->db->fetchAll("
                    SELECT m.*, ml.is_enabled
                    FROM bm_modules m
                    LEFT JOIN bm_module_licenses ml ON m.id = ml.module_id AND ml.user_id = ?
                    WHERE m.is_core = 1 OR ml.is_enabled = 1
                    ORDER BY m.display_order ASC, m.name ASC
                ", [$userId]);
            }
        } catch (\PDOException $e) {
            // Fallback: keine dynamischen Module
            $modules = [];
        }

        // Gruppiere Module
        $navigation = [
            [
                'label' => 'Hauptmenü',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'url' => '/dashboard',
                        'icon' => '📊',
                        'active' => $currentPath === '/dashboard',
                    ],
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
                    [
                        'label' => 'Marketplace',
                        'url' => '/marketplace',
                        'icon' => '🛒',
                        'active' => str_starts_with($currentPath, '/marketplace'),
                    ],
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
                    [
                        'label' => 'Einstellungen',
                        'url' => '/settings',
                        'icon' => '⚙️',
                        'active' => str_starts_with($currentPath, '/settings'),
                    ],
                ],
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
