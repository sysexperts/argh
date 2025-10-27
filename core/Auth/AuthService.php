<?php
/**
 * Auth Service
 * 
 * @package SysExperts\BusinessManager\Auth
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Auth;

use PDO;

class AuthService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Login mit E-Mail/Username und Passwort
     */
    public function login(string $emailOrUsername, string $password): ?User
    {
        // Suche User nach E-Mail
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE email = ? 
            AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$emailOrUsername]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        // Passwort prüfen (password_hash Spalte)
        $passwordField = $userData['password_hash'] ?? $userData['password'] ?? null;
        if (!$passwordField || !password_verify($password, $passwordField)) {
            return null;
        }

        return User::fromArray($userData);
    }

    /**
     * User nach ID laden
     */
    public function getUserById(int $userId): ?User
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE id = ? AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        return User::fromArray($userData);
    }

    /**
     * Registrierung
     */
    public function register(
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        string $companyName
    ): User {
        // Prüfe ob E-Mail bereits existiert
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new \Exception('Diese E-Mail-Adresse ist bereits registriert.');
        }

        // Erstelle neuen Tenant
        $stmt = $this->pdo->prepare("
            INSERT INTO tenants (name, domain, created_at, updated_at)
            VALUES (?, ?, datetime('now'), datetime('now'))
        ");
        $stmt->execute([$companyName, strtolower(str_replace(' ', '-', $companyName)) . '.local']);
        $tenantId = (int)$this->pdo->lastInsertId();

        // Erstelle User
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $verificationToken = bin2hex(random_bytes(32));

        $stmt = $this->pdo->prepare("
            INSERT INTO users (
                tenant_id, email, password_hash, first_name, last_name,
                role, is_active, email_verified_at, email_verification_token,
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, 'admin', 1, NULL, ?, datetime('now'), datetime('now'))
        ");
        
        $stmt->execute([
            $tenantId,
            $email,
            $hashedPassword,
            $firstName,
            $lastName,
            $verificationToken
        ]);

        $userId = (int)$this->pdo->lastInsertId();

        // Lade und gebe User zurück
        return $this->getUserById($userId);
    }

    /**
     * E-Mail verifizieren
     */
    public function verifyEmail(string $token): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT id FROM users 
            WHERE email_verification_token = ? 
            AND email_verified_at IS NULL
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new \Exception('Ungültiger oder bereits verwendeter Verifizierungs-Token.');
        }

        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET email_verified_at = datetime('now'), 
                email_verification_token = NULL,
                updated_at = datetime('now')
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);

        return true;
    }
}
