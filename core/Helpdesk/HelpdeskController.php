<?php
/**
 * Helpdesk/Ticketing Controller
 * 
 * @package SysExperts\BusinessManager\Helpdesk
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Helpdesk;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;
use SysExperts\BusinessManager\Auth\LicenseChecker;
use SysExperts\BusinessManager\Navigation\NavigationService;

class HelpdeskController
{
    private Database $db;
    private SessionService $session;

    public function __construct(Database $db, SessionService $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Ticket-Übersicht
     */
    public function index(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        
        // Lizenzprüfung
        $licenseChecker = new LicenseChecker($this->db);
        if (!$licenseChecker->hasLicense($user['id'], 'helpdesk', $user['role'] ?? 'user')) {
            $_SESSION['error'] = 'Sie haben keine Lizenz für das Helpdesk-Modul. Bitte aktivieren Sie es im Marketplace.';
            return $response->withHeader('Location', '/marketplace')->withStatus(302);
        }

        $tenantId = $user['tenant_id'] ?? 1;
        
        // Filter aus Query-Parametern
        $params = $request->getQueryParams();
        $status = $params['status'] ?? 'all';
        $priority = $params['priority'] ?? 'all';
        
        // Hole Tickets
        $query = "
            SELECT t.*, 
                   c.company_name as customer_name,
                   u.first_name || ' ' || u.last_name as assigned_name,
                   creator.first_name || ' ' || creator.last_name as creator_name
            FROM bm_tickets t
            LEFT JOIN bm_customers c ON t.customer_id = c.id
            LEFT JOIN users u ON t.assigned_to = u.id
            LEFT JOIN users creator ON t.created_by = creator.id
            WHERE t.tenant_id = ?
        ";
        
        $queryParams = [$tenantId];
        
        if ($status !== 'all') {
            $query .= " AND t.status = ?";
            $queryParams[] = $status;
        }
        
        if ($priority !== 'all') {
            $query .= " AND t.priority = ?";
            $queryParams[] = $priority;
        }
        
        $query .= " ORDER BY 
            CASE t.priority 
                WHEN 'critical' THEN 1
                WHEN 'high' THEN 2
                WHEN 'normal' THEN 3
                WHEN 'low' THEN 4
            END,
            t.created_at DESC
        ";
        
        $tickets = $this->db->fetchAll($query, $queryParams);
        
        // Statistiken
        $stats = [
            'open' => $this->db->fetchOne('SELECT COUNT(*) as count FROM bm_tickets WHERE tenant_id = ? AND status = "open"', [$tenantId])['count'] ?? 0,
            'in_progress' => $this->db->fetchOne('SELECT COUNT(*) as count FROM bm_tickets WHERE tenant_id = ? AND status = "in_progress"', [$tenantId])['count'] ?? 0,
            'waiting' => $this->db->fetchOne('SELECT COUNT(*) as count FROM bm_tickets WHERE tenant_id = ? AND status = "waiting"', [$tenantId])['count'] ?? 0,
            'resolved' => $this->db->fetchOne('SELECT COUNT(*) as count FROM bm_tickets WHERE tenant_id = ? AND status = "resolved"', [$tenantId])['count'] ?? 0,
        ];

        // Navigation
        $navService = new NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/helpdesk', $user['role'] ?? 'user');

        ob_start();
        require __DIR__ . '/../../resources/views/helpdesk/index.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Ticket-Details
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        $tenantId = $user['tenant_id'] ?? 1;
        $ticketId = (int) $args['id'];
        
        // Hole Ticket
        $ticket = $this->db->fetchOne("
            SELECT t.*, 
                   c.company_name as customer_name, c.email as customer_email,
                   u.first_name || ' ' || u.last_name as assigned_name,
                   creator.first_name || ' ' || creator.last_name as creator_name
            FROM bm_tickets t
            LEFT JOIN bm_customers c ON t.customer_id = c.id
            LEFT JOIN users u ON t.assigned_to = u.id
            LEFT JOIN users creator ON t.created_by = creator.id
            WHERE t.id = ? AND t.tenant_id = ?
        ", [$ticketId, $tenantId]);
        
        if (!$ticket) {
            $_SESSION['error'] = 'Ticket nicht gefunden';
            return $response->withHeader('Location', '/helpdesk')->withStatus(302);
        }
        
        // Hole Kommentare
        $comments = $this->db->fetchAll("
            SELECT c.*, u.first_name || ' ' || u.last_name as user_name
            FROM bm_ticket_comments c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.ticket_id = ?
            ORDER BY c.created_at ASC
        ", [$ticketId]);
        
        // Hole History
        $history = $this->db->fetchAll("
            SELECT h.*, u.first_name || ' ' || u.last_name as user_name
            FROM bm_ticket_history h
            LEFT JOIN users u ON h.user_id = u.id
            WHERE h.ticket_id = ?
            ORDER BY h.created_at DESC
        ", [$ticketId]);
        
        // Hole alle User für Zuweisung
        $users = $this->db->fetchAll("
            SELECT id, first_name || ' ' || last_name as name
            FROM users
            WHERE tenant_id = ? AND is_active = 1
            ORDER BY first_name, last_name
        ", [$tenantId]);

        // Navigation
        $navService = new NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/helpdesk', $user['role'] ?? 'user');

        ob_start();
        require __DIR__ . '/../../resources/views/helpdesk/show.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Neues Ticket erstellen
     */
    public function store(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        $tenantId = $user['tenant_id'] ?? 1;
        $data = $request->getParsedBody();

        // Ticket-Nummer generieren
        $ticketNumber = 'T-' . date('Ymd') . '-' . str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);

        try {
            $this->db->insert('bm_tickets', [
                'tenant_id' => $tenantId,
                'ticket_number' => $ticketNumber,
                'customer_id' => !empty($data['customer_id']) ? (int)$data['customer_id'] : null,
                'assigned_to' => !empty($data['assigned_to']) ? (int)$data['assigned_to'] : null,
                'created_by' => $user['id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => 'open',
                'priority' => $data['priority'] ?? 'normal',
                'category' => $data['category'] ?? null,
            ]);

            $ticketId = $this->db->getConnection()->lastInsertId();

            // History-Eintrag
            $this->db->insert('bm_ticket_history', [
                'ticket_id' => $ticketId,
                'user_id' => $user['id'],
                'action' => 'created',
                'new_value' => 'Ticket erstellt',
            ]);

            $_SESSION['success'] = 'Ticket erfolgreich erstellt';
            return $response->withHeader('Location', '/helpdesk/' . $ticketId)->withStatus(302);

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Fehler beim Erstellen des Tickets: ' . $e->getMessage();
            return $response->withHeader('Location', '/helpdesk')->withStatus(302);
        }
    }

    /**
     * Kommentar hinzufügen
     */
    public function addComment(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        $ticketId = (int) $args['id'];
        $data = $request->getParsedBody();

        try {
            $this->db->insert('bm_ticket_comments', [
                'ticket_id' => $ticketId,
                'user_id' => $user['id'],
                'comment' => $data['comment'],
                'is_internal' => isset($data['is_internal']) ? 1 : 0,
            ]);

            // Ticket aktualisieren
            $this->db->update('bm_tickets', [
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$ticketId]);

            $_SESSION['success'] = 'Kommentar hinzugefügt';

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Fehler beim Hinzufügen des Kommentars';
        }

        return $response->withHeader('Location', '/helpdesk/' . $ticketId)->withStatus(302);
    }

    /**
     * Ticket-Status ändern
     */
    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        $ticketId = (int) $args['id'];
        $data = $request->getParsedBody();
        $newStatus = $data['status'];

        try {
            // Hole alten Status
            $ticket = $this->db->fetchOne('SELECT status FROM bm_tickets WHERE id = ?', [$ticketId]);
            $oldStatus = $ticket['status'];

            // Update
            $updateData = [
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($newStatus === 'resolved') {
                $updateData['resolved_at'] = date('Y-m-d H:i:s');
            } elseif ($newStatus === 'closed') {
                $updateData['closed_at'] = date('Y-m-d H:i:s');
            }

            $this->db->update('bm_tickets', $updateData, 'id = ?', [$ticketId]);

            // History
            $this->db->insert('bm_ticket_history', [
                'ticket_id' => $ticketId,
                'user_id' => $user['id'],
                'action' => 'status_changed',
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
            ]);

            $_SESSION['success'] = 'Status aktualisiert';

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Fehler beim Aktualisieren des Status';
        }

        return $response->withHeader('Location', '/helpdesk/' . $ticketId)->withStatus(302);
    }

    /**
     * Ticket zuweisen
     */
    public function assign(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        $ticketId = (int) $args['id'];
        $data = $request->getParsedBody();
        $assignedTo = !empty($data['assigned_to']) ? (int)$data['assigned_to'] : null;

        try {
            // Hole alten Wert
            $ticket = $this->db->fetchOne('SELECT assigned_to FROM bm_tickets WHERE id = ?', [$ticketId]);
            $oldAssigned = $ticket['assigned_to'];

            // Update
            $this->db->update('bm_tickets', [
                'assigned_to' => $assignedTo,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$ticketId]);

            // History
            $this->db->insert('bm_ticket_history', [
                'ticket_id' => $ticketId,
                'user_id' => $user['id'],
                'action' => 'assigned',
                'old_value' => $oldAssigned,
                'new_value' => $assignedTo,
            ]);

            $_SESSION['success'] = 'Ticket zugewiesen';

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Fehler beim Zuweisen des Tickets';
        }

        return $response->withHeader('Location', '/helpdesk/' . $ticketId)->withStatus(302);
    }
}
