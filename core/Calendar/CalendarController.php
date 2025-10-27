<?php
/**
 * Calendar Controller
 * 
 * @package SysExperts\BusinessManager\Calendar
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Calendar;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;
use SysExperts\BusinessManager\Auth\LicenseChecker;
use SysExperts\BusinessManager\Navigation\NavigationService;

class CalendarController
{
    private Database $db;
    private SessionService $session;

    public function __construct(Database $db, SessionService $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Kalender-Übersicht
     */
    public function index(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        
        // Lizenzprüfung
        $licenseChecker = new LicenseChecker($this->db);
        if (!$licenseChecker->hasLicense($user['id'], 'calendar', $user['role'] ?? 'user')) {
            $_SESSION['error'] = 'Sie haben keine Lizenz für das Kalender-Modul. Bitte aktivieren Sie es im Marketplace.';
            return $response->withHeader('Location', '/marketplace')->withStatus(302);
        }

        $tenantId = $user['tenant_id'] ?? 1;
        
        // Query-Parameter
        $params = $request->getQueryParams();
        $view = $params['view'] ?? 'month';
        $date = $params['date'] ?? date('Y-m-d');

        // Navigation
        $navService = new NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/calendar', $user['role'] ?? 'user');

        ob_start();
        require __DIR__ . '/../../resources/views/calendar/index.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Events als JSON abrufen (für FullCalendar)
     */
    public function getEvents(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $user = $this->session->getUser();
        $tenantId = $user['tenant_id'] ?? 1;
        
        $params = $request->getQueryParams();
        $start = $params['start'] ?? null;
        $end = $params['end'] ?? null;

        $query = "
            SELECT 
                e.*,
                c.company_name as customer_name,
                u.first_name || ' ' || u.last_name as creator_name
            FROM bm_calendar_events e
            LEFT JOIN bm_customers c ON e.customer_id = c.id
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.tenant_id = ?
        ";
        
        $queryParams = [$tenantId];
        
        if ($start && $end) {
            $query .= " AND e.start_datetime >= ? AND e.end_datetime <= ?";
            $queryParams[] = $start;
            $queryParams[] = $end;
        }
        
        $query .= " ORDER BY e.start_datetime ASC";
        
        $events = $this->db->fetchAll($query, $queryParams);
        
        // Format für FullCalendar
        $formattedEvents = array_map(function($event) {
            return [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start_datetime'],
                'end' => $event['end_datetime'],
                'allDay' => (bool)$event['is_all_day'],
                'backgroundColor' => $event['color'],
                'borderColor' => $event['color'],
                'extendedProps' => [
                    'description' => $event['description'],
                    'location' => $event['location'],
                    'category' => $event['category'],
                    'status' => $event['status'],
                    'customer_name' => $event['customer_name'],
                    'creator_name' => $event['creator_name'],
                ]
            ];
        }, $events);

        $response->getBody()->write(json_encode($formattedEvents));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Event erstellen
     */
    public function store(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $user = $this->session->getUser();
        $tenantId = $user['tenant_id'] ?? 1;
        $data = $request->getParsedBody();

        try {
            $this->db->insert('bm_calendar_events', [
                'tenant_id' => $tenantId,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'location' => $data['location'] ?? null,
                'start_datetime' => $data['start'],
                'end_datetime' => $data['end'],
                'is_all_day' => isset($data['allDay']) ? 1 : 0,
                'category' => $data['category'] ?? null,
                'color' => $data['color'] ?? '#14b8a6',
                'status' => 'planned',
                'customer_id' => !empty($data['customer_id']) ? (int)$data['customer_id'] : null,
                'created_by' => $user['id'],
            ]);
            
            $eventId = $this->db->getConnection()->lastInsertId();

            $response->getBody()->write(json_encode([
                'success' => true,
                'id' => $eventId
            ]));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Event aktualisieren
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated()) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $eventId = (int) $args['id'];
        $data = $request->getParsedBody();

        try {
            $updateData = [
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (isset($data['title'])) $updateData['title'] = $data['title'];
            if (isset($data['description'])) $updateData['description'] = $data['description'];
            if (isset($data['location'])) $updateData['location'] = $data['location'];
            if (isset($data['start'])) $updateData['start_datetime'] = $data['start'];
            if (isset($data['end'])) $updateData['end_datetime'] = $data['end'];
            if (isset($data['category'])) $updateData['category'] = $data['category'];
            if (isset($data['color'])) $updateData['color'] = $data['color'];
            if (isset($data['status'])) $updateData['status'] = $data['status'];

            $this->db->update('bm_calendar_events', $updateData, 'id = ?', [$eventId]);

            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Event löschen
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated()) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $eventId = (int) $args['id'];

        try {
            $this->db->delete('bm_calendar_events', 'id = ?', [$eventId]);

            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
