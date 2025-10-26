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
        
        // Hole alle Benutzer mit Lizenz-Anzahl
        $users = $this->db->fetchAll("
            SELECT u.id, u.email, u.first_name, u.last_name, u.role, u.is_active, 
                   u.created_at,
                   COUNT(ml.id) as active_licenses
            FROM users u
            LEFT JOIN bm_module_licenses ml ON u.id = ml.user_id AND ml.is_enabled = 1
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");

        // Navigation
        $navService = new NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/users', $user['role']);

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
        $existing = $this->db->fetchOne('SELECT id FROM users WHERE email = ?', [$data['email']]);
        if ($existing) {
            $_SESSION['errors'] = ['E-Mail-Adresse wird bereits verwendet'];
            return $response->withHeader('Location', '/users')->withStatus(302);
        }

        // Erstelle Benutzer
        $userId = $this->db->insert('users', [
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

        $this->db->update('users', $updateData, 'id = ?', [$userId]);

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
        $this->db->update('users', ['is_active' => 0], 'id = ?', [$userId]);

        $_SESSION['success'] = 'Benutzer wurde deaktiviert';
        return $response->withHeader('Location', '/users')->withStatus(302);
    }

    /**
     * Benutzer aktivieren
     */
    public function activate(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $args['id'];

        $this->db->update('users', ['is_active' => 1], 'id = ?', [$userId]);

        $_SESSION['success'] = 'Benutzer wurde aktiviert';
        return $response->withHeader('Location', '/users')->withStatus(302);
    }

    /**
     * Lizenzen eines Benutzers verwalten
     */
    public function manageLicenses(Request $request, Response $response, array $args): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $currentUser = $this->session->getUser();
        $userId = (int) $args['id'];

        // Hole Benutzer-Daten
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            $_SESSION['error'] = 'Benutzer nicht gefunden';
            return $response->withHeader('Location', '/users')->withStatus(302);
        }

        // Hole alle Module mit Lizenz-Status für diesen User
        $modules = $this->db->fetchAll("
            SELECT m.*, 
                   ml.is_enabled,
                   ml.id as license_id,
                   ml.created_at as licensed_at
            FROM bm_modules m
            LEFT JOIN bm_module_licenses ml ON m.id = ml.module_id AND ml.user_id = ?
            WHERE m.is_core = 0
            ORDER BY m.category, m.display_order, m.name
        ", [$userId]);

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
        $navigation = $navService->getNavigation($currentUser['id'], '/users', $currentUser['role']);

        ob_start();
        require __DIR__ . '/../../resources/views/users/licenses.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Lizenz für User aktivieren
     */
    public function grantLicense(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $args['user_id'];
        $moduleId = (int) $args['module_id'];

        // Prüfe ob Lizenz bereits existiert
        $existing = $this->db->fetchOne(
            'SELECT id FROM bm_module_licenses WHERE module_id = ? AND user_id = ?',
            [$moduleId, $userId]
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
                'tenant_id' => 1,
                'module_id' => $moduleId,
                'user_id' => $userId,
                'is_enabled' => 1,
            ]);
        }

        $_SESSION['success'] = 'Lizenz wurde erteilt';
        return $response->withHeader('Location', '/users/' . $userId . '/licenses')->withStatus(302);
    }

    /**
     * Lizenz für User entziehen
     */
    public function revokeLicense(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $args['user_id'];
        $moduleId = (int) $args['module_id'];

        // Deaktiviere Lizenz
        $this->db->update(
            'bm_module_licenses',
            ['is_enabled' => 0],
            'module_id = ? AND user_id = ?',
            [$moduleId, $userId]
        );

        $_SESSION['success'] = 'Lizenz wurde entzogen';
        return $response->withHeader('Location', '/users/' . $userId . '/licenses')->withStatus(302);
    }
}
