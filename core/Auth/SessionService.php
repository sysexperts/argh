<?php
/**
 * Session Service
 * 
 * @package SysExperts\BusinessManager\Auth
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Auth;

class SessionService
{
    public function __construct()
    {
        // Starte Session falls noch nicht gestartet
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Login User
     */
    public function login(array $user, bool $rememberMe = false): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $_SESSION['user_role'] = $user['role'] ?? 'user';
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();

        // Regeneriere Session-ID für Sicherheit
        session_regenerate_id(true);

        // Remember Me (optional, später implementieren)
        if ($rememberMe) {
            // TODO: Implement remember me token
        }
    }

    /**
     * Logout User
     */
    public function logout(): void
    {
        $_SESSION = [];
        
        // Lösche Session-Cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }

    /**
     * Prüfe ob User eingeloggt ist
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    /**
     * Hole aktuellen User
     */
    public function getUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'role' => $_SESSION['user_role'] ?? 'user',
        ];
    }

    /**
     * Prüfe ob User Admin ist
     */
    public function isAdmin(): bool
    {
        return $this->isAuthenticated() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    /**
     * Hole User-ID
     */
    public function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
}
