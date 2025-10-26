<?php
/**
 * Auth Controller
 * 
 * @package SysExperts\BusinessManager\Auth
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Core\Database;

class AuthController
{
    private AuthService $authService;
    private SessionManager $sessionManager;

    public function __construct(AuthService $authService, SessionManager $sessionManager)
    {
        $this->authService = $authService;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Zeige Login-Formular
     */
    public function showLogin(Request $request, Response $response): Response
    {
        // Wenn bereits eingeloggt, redirect zu Dashboard
        $currentUser = $this->sessionManager->getCurrentUser($this->authService);
        if ($currentUser) {
            return $response
                ->withHeader('Location', '/dashboard')
                ->withStatus(302);
        }

        ob_start();
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        require __DIR__ . '/../../resources/views/auth/login.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Login-Handler
     */
    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // Validierung
        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Bitte E-Mail und Passwort eingeben.';
            return $response
                ->withHeader('Location', '/auth/login')
                ->withStatus(302);
        }

        // Login versuchen
        try {
            $user = $this->authService->login($email, $password);
            
            if (!$user) {
                $_SESSION['login_error'] = 'Ungültige Anmeldedaten.';
                return $response
                    ->withHeader('Location', '/auth/login')
                    ->withStatus(302);
            }

            // E-Mail-Verifizierung prüfen (vorerst deaktiviert für Test-User)
            // if (!$user->isEmailVerified()) {
            //     $_SESSION['login_error'] = 'Bitte bestätigen Sie zuerst Ihre E-Mail-Adresse.';
            //     return $response
            //         ->withHeader('Location', '/auth/login')
            //         ->withStatus(302);
            // }

            // Session erstellen (Datenbank)
            $this->sessionManager->createSession($user);

            // Auch PHP-Session setzen für Kompatibilität mit alten Controllern
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_email'] = $user->getEmail();
            $_SESSION['user_name'] = $user->getFullName();
            $_SESSION['user_role'] = $user->getRole();
            $_SESSION['authenticated'] = true;
            $_SESSION['login_time'] = time();

            return $response
                ->withHeader('Location', '/dashboard')
                ->withStatus(302);
                
        } catch (\Exception $e) {
            $_SESSION['login_error'] = 'Login-Fehler: ' . $e->getMessage();
            return $response
                ->withHeader('Location', '/auth/login')
                ->withStatus(302);
        }
    }

    /**
     * Logout-Handler
     */
    public function logout(Request $request, Response $response): Response
    {
        $sessionId = $this->sessionManager->getCurrentSessionId();
        if ($sessionId) {
            $this->sessionManager->destroySession($sessionId);
        }

        return $response
            ->withHeader('Location', '/auth/login')
            ->withStatus(302);
    }

    /**
     * Registrierung anzeigen
     */
    public function showRegister(Request $request, Response $response): Response
    {
        ob_start();
        $error = $_SESSION['register_error'] ?? null;
        $success = $_SESSION['register_success'] ?? null;
        unset($_SESSION['register_error'], $_SESSION['register_success']);
        require __DIR__ . '/../../resources/views/auth/register.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Registrierung verarbeiten
     */
    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Validierung
        $errors = [];
        if (empty($data['email'])) $errors[] = 'E-Mail ist erforderlich';
        if (empty($data['password'])) $errors[] = 'Passwort ist erforderlich';
        if (empty($data['first_name'])) $errors[] = 'Vorname ist erforderlich';
        if (empty($data['last_name'])) $errors[] = 'Nachname ist erforderlich';
        if (($data['password'] ?? '') !== ($data['password_confirm'] ?? '')) {
            $errors[] = 'Passwörter stimmen nicht überein';
        }

        if (!empty($errors)) {
            $_SESSION['register_error'] = implode(', ', $errors);
            return $response
                ->withHeader('Location', '/auth/register')
                ->withStatus(302);
        }

        try {
            $user = $this->authService->register($data);
            
            // TODO: E-Mail mit Bestätigungslink senden
            
            $_SESSION['register_success'] = 'Registrierung erfolgreich! Bitte bestätigen Sie Ihre E-Mail-Adresse.';
            return $response
                ->withHeader('Location', '/auth/login')
                ->withStatus(302);
                
        } catch (\Exception $e) {
            $_SESSION['register_error'] = $e->getMessage();
            return $response
                ->withHeader('Location', '/auth/register')
                ->withStatus(302);
        }
    }

    /**
     * E-Mail verifizieren
     */
    public function verifyEmail(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $token = $params['token'] ?? '';

        if (empty($token)) {
            $_SESSION['login_error'] = 'Ungültiger Bestätigungslink.';
            return $response
                ->withHeader('Location', '/auth/login')
                ->withStatus(302);
        }

        if ($this->authService->verifyEmail($token)) {
            $_SESSION['login_success'] = 'E-Mail erfolgreich bestätigt! Sie können sich jetzt anmelden.';
        } else {
            $_SESSION['login_error'] = 'Bestätigungslink ist ungültig oder abgelaufen.';
        }

        return $response
            ->withHeader('Location', '/auth/login')
            ->withStatus(302);
    }
}
