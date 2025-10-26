<?php

namespace SysExperts\BusinessManager\Auth;

use PDO;

/**
 * Session Management Service
 */
class SessionManager
{
    private PDO $db;
    private int $sessionLifetime = 7200; // 2 Stunden

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Session erstellen
     */
    public function createSession(User $user): string
    {
        $sessionId = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $this->sessionLifetime);

        $stmt = $this->db->prepare("
            INSERT INTO sessions (id, user_id, ip_address, user_agent, expires_at)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $sessionId,
            $user->getId(),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $expiresAt
        ]);

        // Session-Cookie setzen
        setcookie('session_id', $sessionId, [
            'expires' => time() + $this->sessionLifetime,
            'path' => '/',
            'httponly' => true,
            'secure' => isset($_SERVER['HTTPS']),
            'samesite' => 'Lax'
        ]);

        return $sessionId;
    }

    /**
     * Session validieren
     */
    public function validateSession(string $sessionId): ?int
    {
        $stmt = $this->db->prepare("
            SELECT user_id FROM sessions 
            WHERE id = ? AND expires_at > ?
        ");
        $stmt->execute([$sessionId, date('Y-m-d H:i:s')]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            return null;
        }

        // Last Activity aktualisieren
        $this->updateSessionActivity($sessionId);

        return (int)$session['user_id'];
    }

    /**
     * Session verlängern
     */
    private function updateSessionActivity(string $sessionId): void
    {
        $expiresAt = date('Y-m-d H:i:s', time() + $this->sessionLifetime);

        $stmt = $this->db->prepare("
            UPDATE sessions 
            SET last_activity = ?, expires_at = ? 
            WHERE id = ?
        ");
        $stmt->execute([date('Y-m-d H:i:s'), $expiresAt, $sessionId]);
    }

    /**
     * Session beenden
     */
    public function destroySession(string $sessionId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
        $result = $stmt->execute([$sessionId]);

        // Cookie löschen
        setcookie('session_id', '', [
            'expires' => time() - 3600,
            'path' => '/',
        ]);

        return $result;
    }

    /**
     * Alle Sessions eines Benutzers beenden
     */
    public function destroyAllUserSessions(int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Abgelaufene Sessions löschen
     */
    public function cleanupExpiredSessions(): int
    {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE expires_at < ?");
        $stmt->execute([date('Y-m-d H:i:s')]);
        return $stmt->rowCount();
    }

    /**
     * Aktuelle Session-ID aus Cookie holen
     */
    public function getCurrentSessionId(): ?string
    {
        return $_COOKIE['session_id'] ?? null;
    }

    /**
     * Aktuellen Benutzer aus Session laden
     */
    public function getCurrentUser(AuthService $authService): ?User
    {
        $sessionId = $this->getCurrentSessionId();
        if (!$sessionId) {
            return null;
        }

        $userId = $this->validateSession($sessionId);
        if (!$userId) {
            return null;
        }

        return $authService->getUserById($userId);
    }
}
