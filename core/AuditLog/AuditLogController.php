<?php
/**
 * Audit-Log Controller
 * Zeigt Audit-Logs an (nur für Admins)
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\AuditLog;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;
use SysExperts\BusinessManager\Navigation\NavigationService;

class AuditLogController
{
    private Database $db;
    private SessionService $session;
    private AuditLogService $auditService;

    public function __construct(Database $db, SessionService $session)
    {
        $this->db = $db;
        $this->session = $session;
        $this->auditService = new AuditLogService($db);
    }

    /**
     * Zeige Audit-Logs
     */
    public function index(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        
        // Nur Admins dürfen Audit-Logs sehen
        if ($user['role'] !== 'admin') {
            $_SESSION['error'] = 'Keine Berechtigung für Audit-Logs.';
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        // Filter aus Query-Parametern
        $params = $request->getQueryParams();
        $filters = [
            'action' => $params['action'] ?? null,
            'entity_type' => $params['entity_type'] ?? null,
            'severity' => $params['severity'] ?? null,
            'date_from' => $params['date_from'] ?? null,
            'date_to' => $params['date_to'] ?? null,
        ];

        // Entferne leere Filter
        $filters = array_filter($filters);

        // Pagination
        $page = (int)($params['page'] ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        // Hole Logs
        $logs = $this->auditService->getLogs($filters, $perPage, $offset);
        $totalLogs = $this->auditService->countLogs($filters);
        $totalPages = ceil($totalLogs / $perPage);

        // Navigation
        $navService = new NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/audit-logs', $user['role'] ?? 'user');

        // Stelle sicher dass alle Variablen definiert sind
        $filters = $filters ?? [];
        $logs = $logs ?? [];
        $totalLogs = $totalLogs ?? 0;
        $totalPages = $totalPages ?? 1;
        $page = $page ?? 1;
        
        $pageTitle = 'Audit-Logs';
        $title = 'Audit-Logs - Revisionssicher';
        
        ob_start();
        require __DIR__ . '/../../resources/views/audit-logs/index.php';
        $content = ob_get_clean();
        
        ob_start();
        require __DIR__ . '/../../resources/views/layouts/app.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Exportiere Logs als CSV
     */
    public function export(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        
        if ($user['role'] !== 'admin') {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }

        $params = $request->getQueryParams();
        $filters = array_filter([
            'date_from' => $params['date_from'] ?? null,
            'date_to' => $params['date_to'] ?? null,
        ]);

        $csv = $this->auditService->exportLogs($filters);

        $response->getBody()->write($csv);
        return $response
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="audit-logs-' . date('Y-m-d') . '.csv"');
    }
}
