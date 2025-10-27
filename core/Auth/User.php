<?php
/**
 * User Entity
 * 
 * @package SysExperts\BusinessManager\Auth
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Auth;

class User
{
    private int $id;
    private int $tenantId;
    private string $email;
    private string $username;
    private string $firstName;
    private string $lastName;
    private string $role;
    private bool $isActive;
    private ?string $emailVerifiedAt;

    private function __construct(
        int $id,
        int $tenantId,
        string $email,
        string $username,
        string $firstName,
        string $lastName,
        string $role,
        bool $isActive,
        ?string $emailVerifiedAt
    ) {
        $this->id = $id;
        $this->tenantId = $tenantId;
        $this->email = $email;
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->role = $role;
        $this->isActive = $isActive;
        $this->emailVerifiedAt = $emailVerifiedAt;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (int)$data['tenant_id'],
            $data['email'],
            $data['username'] ?? '',
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['role'] ?? 'user',
            (bool)$data['is_active'],
            $data['email_verified_at'] ?? null
        );
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

    public function getUsername(): string
    {
        return $this->username;
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
        return trim($this->firstName . ' ' . $this->lastName);
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
        return $this->emailVerifiedAt !== null;
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenantId,
            'email' => $this->email,
            'username' => $this->username,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->getFullName(),
            'role' => $this->role,
            'is_active' => $this->isActive,
            'email_verified' => $this->isEmailVerified(),
        ];
    }
}
