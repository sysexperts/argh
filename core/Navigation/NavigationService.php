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

        // Hole alle Module mit Lizenzen für diesen User
        $stmt = $pdo->prepare("
            SELECT 
                m.id,
                m.code,
                m.name,
                m.icon,
                m.url,
                m.category,
                m.display_order,
                ml.is_enabled
            FROM bm_modules m
            LEFT JOIN bm_module_licenses ml ON m.id = ml.module_id AND ml.user_id = ?
            WHERE m.is_active = 1 
            AND (m.is_core = 1 OR ml.is_enabled = 1)
            ORDER BY m.display_order ASC, m.name ASC
        ");
        $stmt->execute([$userId]);
        $modules = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Gruppiere nach Kategorie für Layout-Format
        $grouped = [];
        foreach ($modules as $module) {
            $category = $module['category'] ?? 'Sonstiges';
            if (!isset($grouped[$category])) {
                $grouped[$category] = ['items' => []];
            }
            $grouped[$category]['items'][] = [
                'id' => $module['id'],
                'code' => $module['code'],
                'label' => $module['name'],
                'name' => $module['name'],
                'icon' => $module['icon'] ?? '📦',
                'url' => $module['url'] ?? '/' . strtolower($module['code']),
                'route' => $module['url'] ?? '/' . strtolower($module['code']),
                'active' => ($module['url'] ?? '') === $currentPath,
                'is_active' => ($module['url'] ?? '') === $currentPath,
                'is_enabled' => (bool)$module['is_enabled'],
            ];
        }

        return $grouped;
    }
}
