<?php
/**
 * Session Manager
 * 
 * @package SysExperts\BusinessManager\Auth
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Auth;

use PDO;

class SessionManager
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Session erstellen
     */
    public function createSession(User $user): string
    {
        $sessionId = bin2hex(random_bytes(32));
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $stmt = $this->pdo->prepare("
            INSERT INTO user_sessions (
                session_id, user_id, ip_address, user_agent, 
                last_activity, created_at
            ) VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$sessionId, $user->getId(), $ipAddress, $userAgent]);

        // Session-ID in PHP-Session speichern
        $_SESSION['session_id'] = $sessionId;
        $_SESSION['user_id'] = $user->getId();

        return $sessionId;
    }

    /**
     * Session zerstören
     */
    public function destroySession(string $sessionId): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM user_sessions WHERE session_id = ?
        ");
        $stmt->execute([$sessionId]);

        // PHP-Session leeren
        session_unset();
        session_destroy();
    }

    /**
     * Aktuelle Session-ID holen
     */
    public function getCurrentSessionId(): ?string
    {
        return $_SESSION['session_id'] ?? null;
    }

    /**
     * Aktuellen User holen
     */
    public function getCurrentUser(AuthService $authService): ?User
    {
        $sessionId = $this->getCurrentSessionId();
        if (!$sessionId) {
            return null;
        }

        // Session aus DB laden
        $stmt = $this->pdo->prepare("
            SELECT user_id FROM user_sessions 
            WHERE session_id = ? 
            AND last_activity > datetime('now', '-24 hours')
        ");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            return null;
        }

        // Last Activity aktualisieren
        $stmt = $this->pdo->prepare("
            UPDATE user_sessions 
            SET last_activity = NOW() 
            WHERE session_id = ?
        ");
        $stmt->execute([$sessionId]);

        // User laden
        return $authService->getUserById((int)$session['user_id']);
    }
}
