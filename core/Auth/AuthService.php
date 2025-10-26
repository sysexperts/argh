<?php

namespace SysExperts\BusinessManager\Auth;

use PDO;

/**
 * Authentication Service
 */
class AuthService
{
    private PDO $db;
    private int $tenantId;

    public function __construct(PDO $db, int $tenantId = 1)
    {
        $this->db = $db;
        $this->tenantId = $tenantId;
    }

    /**
     * Login mit E-Mail und Passwort
     */
    public function login(string $email, string $password): ?User
    {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE email = ? AND tenant_id = ? AND is_active = 1
        ");
        $stmt->execute([$email, $this->tenantId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        // Passwort prüfen
        if (!password_verify($password, $userData['password_hash'])) {
            return null;
        }

        // Last Login aktualisieren
        $this->updateLastLogin($userData['id']);

        return new User($userData);
    }

    /**
     * Benutzer registrieren
     */
    public function register(array $data): ?User
    {
        // Prüfe ob E-Mail bereits existiert
        if ($this->emailExists($data['email'])) {
            throw new \Exception('E-Mail-Adresse bereits registriert');
        }

        // Passwort hashen
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

        // Verification Token generieren
        $verificationToken = bin2hex(random_bytes(32));
        $verificationExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $stmt = $this->db->prepare("
            INSERT INTO users (
                tenant_id, email, password_hash, first_name, last_name, 
                role, email_verification_token, email_verification_expires
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $this->tenantId,
            $data['email'],
            $passwordHash,
            $data['first_name'],
            $data['last_name'],
            'user', // Neue Registrierungen sind immer normale User, nie Admin
            $verificationToken,
            $verificationExpires
        ]);

        $userId = (int)$this->db->lastInsertId();

        // Benutzer laden
        return $this->getUserById($userId);
    }

    /**
     * E-Mail verifizieren
     */
    public function verifyEmail(string $token): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM users 
            WHERE email_verification_token = ? 
            AND email_verification_expires > ?
            AND tenant_id = ?
        ");
        $stmt->execute([$token, date('Y-m-d H:i:s'), $this->tenantId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        // E-Mail als verifiziert markieren
        $stmt = $this->db->prepare("
            UPDATE users 
            SET email_verified = 1, 
                email_verification_token = NULL, 
                email_verification_expires = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);

        return true;
    }

    /**
     * Passwort-Reset anfordern
     */
    public function requestPasswordReset(string $email): ?string
    {
        $stmt = $this->db->prepare("
            SELECT id FROM users 
            WHERE email = ? AND tenant_id = ? AND is_active = 1
        ");
        $stmt->execute([$email, $this->tenantId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        // Reset Token generieren
        $resetToken = bin2hex(random_bytes(32));
        $resetExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->db->prepare("
            UPDATE users 
            SET password_reset_token = ?, 
                password_reset_expires = ? 
            WHERE id = ?
        ");
        $stmt->execute([$resetToken, $resetExpires, $user['id']]);

        return $resetToken;
    }

    /**
     * Passwort zurücksetzen
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM users 
            WHERE password_reset_token = ? 
            AND password_reset_expires > ?
            AND tenant_id = ?
        ");
        $stmt->execute([$token, date('Y-m-d H:i:s'), $this->tenantId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        // Neues Passwort setzen
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password_hash = ?, 
                password_reset_token = NULL, 
                password_reset_expires = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$passwordHash, $user['id']]);

        return true;
    }

    /**
     * Benutzer nach ID laden
     */
    public function getUserById(int $id): ?User
    {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE id = ? AND tenant_id = ?
        ");
        $stmt->execute([$id, $this->tenantId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $userData ? new User($userData) : null;
    }

    /**
     * Benutzer nach E-Mail laden
     */
    public function getUserByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE email = ? AND tenant_id = ?
        ");
        $stmt->execute([$email, $this->tenantId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $userData ? new User($userData) : null;
    }

    /**
     * Prüfe ob E-Mail existiert
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM users 
            WHERE email = ? AND tenant_id = ?
        ");
        $stmt->execute([$email, $this->tenantId]);
        return $stmt->fetchColumn() > 0;
    }

    public function getAllUsers(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE tenant_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$this->tenantId]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($u) => new User($u), $users);
    }

    /**
     * Benutzer aktualisieren
     */
    public function updateUser(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        if (isset($data['first_name'])) {
            $fields[] = 'first_name = ?';
            $values[] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $fields[] = 'last_name = ?';
            $values[] = $data['last_name'];
        }
        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $values[] = $data['email'];
        }
        if (isset($data['role'])) {
            $fields[] = 'role = ?';
            $values[] = $data['role'];
        }
        if (isset($data['is_active'])) {
            $fields[] = 'is_active = ?';
            $values[] = $data['is_active'] ? 1 : 0;
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $values[] = $this->tenantId;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ? AND tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Benutzer löschen
     */
    public function deleteUser(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM users WHERE id = ? AND tenant_id = ?
        ");
        return $stmt->execute([$id, $this->tenantId]);
    }

    /**
     * Letzten Login aktualisieren
     */
    private function updateLastLogin(int $userId): void
    {
        $stmt = $this->db->prepare("
            UPDATE users SET last_login = ? WHERE id = ?
        ");
        $stmt->execute([date('Y-m-d H:i:s'), $userId]);
    }
}
