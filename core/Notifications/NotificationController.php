<?php
/**
 * Notification Controller
 * 
 * @package SysExperts\BusinessManager\Notifications
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Notifications;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;

class NotificationController
{
    private Database $db;
    private SessionService $session;
    private NotificationService $notificationService;

    public function __construct(Database $db, SessionService $session)
    {
        $this->db = $db;
        $this->session = $session;
        $this->notificationService = new NotificationService($db);
    }

    /**
     * Benachrichtigungen abrufen (JSON)
     */
    public function index(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $user = $this->session->getUser();
        $notifications = $this->notificationService->getUserNotifications($user['id'], 50);
        $unreadCount = $this->notificationService->getUnreadCount($user['id']);

        $response->getBody()->write(json_encode([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Als gelesen markieren
     */
    public function markAsRead(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withStatus(401);
        }

        $notificationId = (int) $args['id'];
        $this->notificationService->markAsRead($notificationId);

        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Alle als gelesen markieren
     */
    public function markAllAsRead(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withStatus(401);
        }

        $user = $this->session->getUser();
        $this->notificationService->markAllAsRead($user['id']);

        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Benachrichtigung löschen
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withStatus(401);
        }

        $notificationId = (int) $args['id'];
        $this->notificationService->delete($notificationId);

        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
