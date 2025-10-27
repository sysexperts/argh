<?php
/**
 * Partner Console Controller
 * Nur für sys-experts.de Admins
 * 
 * @package SysExperts\BusinessManager\PartnerConsole
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\PartnerConsole;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;

class PartnerConsoleController
{
    private Database $db;
    private SessionService $session;

    public function __construct(Database $db, SessionService $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Dashboard - Übersicht aller Mandanten
     */
    public function index(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated() || !$this->isPartnerAdmin()) {
            $_SESSION['error'] = 'Zugriff verweigert. Nur für sys-experts.de Admins.';
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        // Statistiken
        $stats = $this->getStatistics();
        
        // Mandanten mit letztem Heartbeat
        $tenants = $this->db->fetchAll("
            SELECT 
                t.*,
                (SELECT COUNT(*) FROM bm_tenant_licenses WHERE tenant_id = t.id AND is_active = 1) as active_modules,
                (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) as user_count,
                h.created_at as last_heartbeat_time,
                h.heartbeat_status as heartbeat_status
            FROM bm_tenants t
            LEFT JOIN (
                SELECT tenant_id, MAX(created_at) as created_at, heartbeat_status
                FROM bm_tenant_heartbeats
                GROUP BY tenant_id
            ) h ON t.id = h.tenant_id
            ORDER BY t.created_at DESC
        ");

        $user = $this->session->getUser();
        $navService = new \SysExperts\BusinessManager\Navigation\NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/partner-console', $user['role'] ?? 'admin');

        $pageTitle = 'Partner Console';
        $title = 'Partner Console - sys-experts.de';
        
        ob_start();
        require __DIR__ . '/../../resources/views/partner-console/index.php';
        $content = ob_get_clean();
        
        ob_start();
        require __DIR__ . '/../../resources/views/layouts/app.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Mandanten-Details
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated() || !$this->isPartnerAdmin()) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $tenantId = (int) $args['id'];
        
        // Mandant laden
        $tenant = $this->db->fetchOne("SELECT * FROM bm_tenants WHERE id = ?", [$tenantId]);
        
        if (!$tenant) {
            $_SESSION['error'] = 'Mandant nicht gefunden.';
            return $response->withHeader('Location', '/partner-console')->withStatus(302);
        }

        // Lizenzen
        $licenses = $this->db->fetchAll("
            SELECT ml.*, m.name as module_name, m.code as module_code, m.price_per_user
            FROM bm_tenant_licenses ml
            INNER JOIN bm_modules m ON ml.module_id = m.id
            WHERE ml.tenant_id = ?
            ORDER BY m.name
        ", [$tenantId]);

        // Versionen
        $versions = $this->db->fetchAll("
            SELECT * FROM bm_tenant_versions 
            WHERE tenant_id = ? 
            ORDER BY deployed_at DESC 
            LIMIT 20
        ", [$tenantId]);

        // Heartbeats
        $heartbeats = $this->db->fetchAll("
            SELECT * FROM bm_tenant_heartbeats 
            WHERE tenant_id = ? 
            ORDER BY created_at DESC 
            LIMIT 10
        ", [$tenantId]);

        // Notizen
        $notes = $this->db->fetchAll("
            SELECT * FROM bm_tenant_notes 
            WHERE tenant_id = ? 
            ORDER BY created_at DESC
        ", [$tenantId]);

        $user = $this->session->getUser();
        $navService = new \SysExperts\BusinessManager\Navigation\NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/partner-console', $user['role'] ?? 'admin');

        $pageTitle = $tenant['company_name'];
        $title = $tenant['company_name'] . ' - Partner Console';
        
        ob_start();
        require __DIR__ . '/../../resources/views/partner-console/show.php';
        $content = ob_get_clean();
        
        ob_start();
        require __DIR__ . '/../../resources/views/layouts/app.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Neuer Mandant - Formular
     */
    public function create(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated() || !$this->isPartnerAdmin()) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $user = $this->session->getUser();
        $navService = new \SysExperts\BusinessManager\Navigation\NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/partner-console', $user['role'] ?? 'admin');

        $pageTitle = 'Neuer Mandant';
        $title = 'Neuer Mandant - Partner Console';
        
        ob_start();
        require __DIR__ . '/../../resources/views/partner-console/create.php';
        $content = ob_get_clean();
        
        ob_start();
        require __DIR__ . '/../../resources/views/layouts/app.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Mandant speichern
     */
    public function store(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated() || !$this->isPartnerAdmin()) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $data = $request->getParsedBody();

        try {
            $this->db->insert('bm_tenants', [
                'tenant_name' => $data['tenant_name'],
                'company_name' => $data['company_name'],
                'domain' => $data['domain'],
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'] ?? null,
                'tenant_status' => $data['tenant_status'] ?? 'trial',
                'trial_until' => $data['trial_until'] ?? null,
                'server_host' => $data['server_host'] ?? null,
                'server_ip' => $data['server_ip'] ?? null,
                'installed_version' => '1.0.0',
            ]);

            $_SESSION['success'] = 'Mandant erfolgreich erstellt!';
            return $response->withHeader('Location', '/partner-console')->withStatus(302);

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Fehler beim Erstellen: ' . $e->getMessage();
            return $response->withHeader('Location', '/partner-console/tenants/create')->withStatus(302);
        }
    }

    /**
     * Statistiken
     */
    private function getStatistics(): array
    {
        return [
            'total_tenants' => $this->db->fetchOne("SELECT COUNT(*) as count FROM bm_tenants")['count'] ?? 0,
            'active_tenants' => $this->db->fetchOne("SELECT COUNT(*) as count FROM bm_tenants WHERE tenant_status = 'active'")['count'] ?? 0,
            'trial_tenants' => $this->db->fetchOne("SELECT COUNT(*) as count FROM bm_tenants WHERE tenant_status = 'trial'")['count'] ?? 0,
            'total_users' => $this->db->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
            'total_licenses' => $this->db->fetchOne("SELECT COUNT(*) as count FROM bm_tenant_licenses WHERE is_active = 1")['count'] ?? 0,
        ];
    }

    /**
     * Prüfe ob User Partner-Admin ist
     */
    private function isPartnerAdmin(): bool
    {
        if (!$this->session->isAuthenticated()) {
            return false;
        }
        
        $user = $this->session->getUser();
        
        // Hole vollständige User-Daten aus DB
        $userId = $user['id'] ?? null;
        if (!$userId) {
            return false;
        }
        
        $fullUser = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$fullUser) {
            return false;
        }
        
        // Nur Admin-User mit sys-experts.de Email
        return $fullUser['role'] === 'admin' && $fullUser['email'] === 'admin@sys-experts.de';
    }
}
