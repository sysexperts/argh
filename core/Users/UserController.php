<?php
/**
 * User Controller
 * 
 * @package SysExperts\BusinessManager\Users
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Users;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;
use SysExperts\BusinessManager\Navigation\NavigationService;

class UserController
{
    private Database $db;
    private SessionService $session;

    public function __construct(Database $db, SessionService $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Liste aller Benutzer
     */
    public function index(Request $request, Response $response): Response
    {
        // Auth-Check
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        
        // Hole alle Benutzer
        $users = $this->db->fetchAll("
            SELECT id, email, first_name, last_name, role, is_active, 
                   created_at, last_login_at
            FROM bm_users
            ORDER BY created_at DESC
        ");

        // Navigation
        $navService = new NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/users');

        // Render View
        ob_start();
        require __DIR__ . '/../../resources/views/users/index.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Benutzer erstellen
     */
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Validierung
        $errors = [];
        if (empty($data['email'])) {
            $errors[] = 'E-Mail ist erforderlich';
        }
        if (empty($data['password'])) {
            $errors[] = 'Passwort ist erforderlich';
        }
        if (empty($data['first_name'])) {
            $errors[] = 'Vorname ist erforderlich';
        }
        if (empty($data['last_name'])) {
            $errors[] = 'Nachname ist erforderlich';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            return $response->withHeader('Location', '/users')->withStatus(302);
        }

        // Prüfe ob E-Mail bereits existiert
        $existing = $this->db->fetchOne('SELECT id FROM bm_users WHERE email = ?', [$data['email']]);
        if ($existing) {
            $_SESSION['errors'] = ['E-Mail-Adresse wird bereits verwendet'];
            return $response->withHeader('Location', '/users')->withStatus(302);
        }

        // Erstelle Benutzer
        $userId = $this->db->insert('bm_users', [
            'tenant_id' => 1, // TODO: Aus Session holen
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => $data['role'] ?? 'user',
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ]);

        $_SESSION['success'] = 'Benutzer erfolgreich erstellt';
        return $response->withHeader('Location', '/users')->withStatus(302);
    }

    /**
     * Benutzer aktualisieren
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $args['id'];
        $data = $request->getParsedBody();

        // Validierung
        $errors = [];
        if (empty($data['email'])) {
            $errors[] = 'E-Mail ist erforderlich';
        }
        if (empty($data['first_name'])) {
            $errors[] = 'Vorname ist erforderlich';
        }
        if (empty($data['last_name'])) {
            $errors[] = 'Nachname ist erforderlich';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            return $response->withHeader('Location', '/users')->withStatus(302);
        }

        // Update-Daten
        $updateData = [
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => $data['role'] ?? 'user',
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ];

        // Wenn Passwort gesetzt, auch updaten
        if (!empty($data['password'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $this->db->update('bm_users', $updateData, 'id = ?', [$userId]);

        $_SESSION['success'] = 'Benutzer erfolgreich aktualisiert';
        return $response->withHeader('Location', '/users')->withStatus(302);
    }

    /**
     * Benutzer löschen/deaktivieren
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $args['id'];

        // Soft Delete - nur deaktivieren
        $this->db->update('bm_users', ['is_active' => 0], 'id = ?', [$userId]);

        $_SESSION['success'] = 'Benutzer wurde deaktiviert';
        return $response->withHeader('Location', '/users')->withStatus(302);
    }

    /**
     * Benutzer aktivieren
     */
    public function activate(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $args['id'];

        $this->db->update('bm_users', ['is_active' => 1], 'id = ?', [$userId]);

        $_SESSION['success'] = 'Benutzer wurde aktiviert';
        return $response->withHeader('Location', '/users')->withStatus(302);
    }
}
