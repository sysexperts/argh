<?php
/**
 * Module Controller
 * 
 * @package SysExperts\BusinessManager\Modules
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Modules;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;
use SysExperts\BusinessManager\Navigation\NavigationService;

class ModuleController
{
    private Database $db;
    private SessionService $session;

    public function __construct(Database $db, SessionService $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Modul-Übersicht (Meine Module)
     */
    public function index(Request $request, Response $response): Response
    {
        // Auth-Check
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        
        // Hole alle Module mit Lizenz-Status für aktuellen User
        $modules = $this->db->fetchAll("
            SELECT m.*, 
                   ml.is_enabled,
                   ml.id as license_id
            FROM bm_modules m
            LEFT JOIN bm_module_licenses ml ON m.id = ml.module_id AND ml.user_id = ?
            ORDER BY m.is_core DESC, m.category, m.display_order, m.name
        ", [$user['id']]);

        // Gruppiere nach Kategorie
        $grouped = [];
        foreach ($modules as $module) {
            $category = $module['category'] ?? 'Sonstiges';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $module;
        }

        // Navigation
        $navService = new NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/modules', $user['role']);

        // Render View
        ob_start();
        require __DIR__ . '/../../resources/views/modules/index.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Marketplace (Verfügbare Module)
     */
    public function marketplace(Request $request, Response $response): Response
    {
        // Auth-Check
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        
        // Hole alle Module mit Lizenz-Status
        $modules = $this->db->fetchAll("
            SELECT m.*, 
                   ml.is_enabled,
                   ml.id as license_id
            FROM bm_modules m
            LEFT JOIN bm_module_licenses ml ON m.id = ml.module_id AND ml.user_id = ?
            WHERE m.is_core = 0
            ORDER BY m.category, m.display_order, m.name
        ", [$user['id']]);

        // Gruppiere nach Kategorie
        $grouped = [];
        foreach ($modules as $module) {
            $category = $module['category'] ?? 'Sonstiges';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $module;
        }

        // Navigation
        $navService = new NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/marketplace', $user['role']);

        // Render View
        ob_start();
        require __DIR__ . '/../../resources/views/modules/marketplace.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Modul aktivieren/buchen
     */
    public function activate(Request $request, Response $response, array $args): Response
    {
        $moduleId = (int) $args['id'];
        $user = $this->session->getUser();

        // Prüfe ob Lizenz bereits existiert
        $existing = $this->db->fetchOne(
            'SELECT id FROM bm_module_licenses WHERE module_id = ? AND user_id = ?',
            [$moduleId, $user['id']]
        );

        if ($existing) {
            // Aktiviere bestehende Lizenz
            $this->db->update(
                'bm_module_licenses',
                ['is_enabled' => 1],
                'id = ?',
                [$existing['id']]
            );
        } else {
            // Erstelle neue Lizenz
            $this->db->insert('bm_module_licenses', [
                'tenant_id' => 1, // TODO: Aus Session
                'module_id' => $moduleId,
                'user_id' => $user['id'],
                'is_enabled' => 1,
            ]);
        }

        $_SESSION['success'] = 'Modul wurde aktiviert';
        
        // Redirect zurück
        $referer = $_SERVER['HTTP_REFERER'] ?? '/modules';
        return $response->withHeader('Location', $referer)->withStatus(302);
    }

    /**
     * Modul deaktivieren
     */
    public function deactivate(Request $request, Response $response, array $args): Response
    {
        $moduleId = (int) $args['id'];
        $user = $this->session->getUser();

        // Deaktiviere Lizenz
        $this->db->update(
            'bm_module_licenses',
            ['is_enabled' => 0],
            'module_id = ? AND user_id = ?',
            [$moduleId, $user['id']]
        );

        $_SESSION['success'] = 'Modul wurde deaktiviert';
        
        // Redirect zurück
        $referer = $_SERVER['HTTP_REFERER'] ?? '/modules';
        return $response->withHeader('Location', $referer)->withStatus(302);
    }
}
