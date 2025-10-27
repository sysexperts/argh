<?php
/**
 * Navigation Service
 * 
 * @package SysExperts\BusinessManager\Navigation
 */

declare(strict_types=1);

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
     * Hole Navigation für User
     */
    public function getNavigation(int $userId, string $currentPath = '', string $userRole = 'user'): array
    {
        $pdo = $this->db->getConnection();

        // Core Navigation - immer sichtbar
        $coreNav = [
            'Core' => [
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'url' => '/dashboard',
                        'icon' => 'dashboard',
                        'active' => $currentPath === '/dashboard',
                    ],
                    [
                        'label' => 'Marketplace',
                        'url' => '/modules',
                        'icon' => 'store',
                        'active' => $currentPath === '/modules',
                    ],
                ]
            ]
        ];

        // Admin-spezifische Core-Links
        if ($userRole === 'admin') {
            $coreNav['Core']['items'][] = [
                'label' => 'Benutzerverwaltung',
                'url' => '/users',
                'icon' => 'group',
                'active' => $currentPath === '/users',
            ];
        }

        // Hole aktivierte Module für diesen User
        $stmt = $pdo->prepare("
            SELECT 
                m.id,
                m.code,
                m.name,
                m.url,
                m.category,
                m.display_order
            FROM bm_modules m
            INNER JOIN bm_module_licenses ml ON m.id = ml.module_id
            WHERE m.is_active = 1 
            AND ml.user_id = ?
            AND ml.is_enabled = 1
            ORDER BY m.display_order ASC, m.name ASC
        ");
        $stmt->execute([$userId]);
        $modules = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Icon-Mapping für Module (Material Icons)
        $iconMap = [
            'time-tracking' => 'schedule',
            'invoices' => 'receipt',
            'customers' => 'business',
            'calendar' => 'calendar_month',
            'helpdesk' => 'support_agent',
            'projects' => 'folder',
            'tasks' => 'task',
            'documents' => 'description',
            'audit-logs' => 'history',
        ];

        // Gruppiere Module nach Kategorie
        $moduleNav = [];
        foreach ($modules as $module) {
            $category = $module['category'] ?? 'Module';
            if (!isset($moduleNav[$category])) {
                $moduleNav[$category] = ['items' => []];
            }
            $moduleNav[$category]['items'][] = [
                'label' => $module['name'],
                'url' => $module['url'] ?? '/' . strtolower($module['code']),
                'icon' => $iconMap[$module['code']] ?? 'extension',
                'active' => ($module['url'] ?? '') === $currentPath,
            ];
        }

        // Kombiniere Core + Module Navigation
        return array_merge($coreNav, $moduleNav);
    }
}
