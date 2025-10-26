<?php

namespace SysExperts\BusinessManager\Auth;

/**
 * User Model
 */
class User
{
    private int $id;
    private int $tenantId;
    private string $email;
    private string $firstName;
    private string $lastName;
    private string $role;
    private bool $isActive;
    private bool $emailVerified;
    private ?string $lastLogin;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->tenantId = (int)$data['tenant_id'];
        $this->email = $data['email'];
        $this->firstName = $data['first_name'];
        $this->lastName = $data['last_name'];
        $this->role = $data['role'];
        $this->isActive = (bool)$data['is_active'];
        $this->emailVerified = (bool)$data['email_verified'];
        $this->lastLogin = $data['last_login'] ?? null;
        $this->createdAt = $data['created_at'];
        $this->updatedAt = $data['updated_at'];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTenantId(): int
    {
        return $this->tenantId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function getLastLogin(): ?string
    {
        return $this->lastLogin;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * Prüfe ob Benutzer Admin ist
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Prüfe ob Benutzer Gast ist
     */
    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }

    /**
     * Konvertiere zu Array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->getFullName(),
            'role' => $this->role,
            'is_active' => $this->isActive,
            'email_verified' => $this->emailVerified,
            'last_login' => $this->lastLogin,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Konvertiere zu Array (ohne sensible Daten)
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->getFullName(),
            'name' => $this->getFullName(), // Für Layout-Kompatibilität
            'role' => $this->role,
        ];
    }
}
