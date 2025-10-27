<?php
/**
 * Audit-Logger - Statische Helper-Klasse
 * Für einfache Nutzung im gesamten System
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\AuditLog;

use SysExperts\BusinessManager\Database\Database;

class AuditLogger
{
    private static ?AuditLogService $service = null;

    /**
     * Initialisiere Service
     */
    private static function getService(): AuditLogService
    {
        if (self::$service === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $db = new Database($config);
            self::$service = new AuditLogService($db);
        }
        
        return self::$service;
    }

    /**
     * Schnell-Log Funktionen
     */
    
    public static function logCreate(int $userId, string $entityType, int $entityId, string $description, array $data = []): void
    {
        self::getService()->log(
            $userId,
            'create',
            $entityType,
            $entityId,
            $description,
            null,
            $data,
            'info',
            'data'
        );
    }

    public static function logUpdate(int $userId, string $entityType, int $entityId, string $description, array $oldData, array $newData): void
    {
        self::getService()->log(
            $userId,
            'update',
            $entityType,
            $entityId,
            $description,
            $oldData,
            $newData,
            'info',
            'data'
        );
    }

    public static function logDelete(int $userId, string $entityType, int $entityId, string $description, array $data = []): void
    {
        self::getService()->log(
            $userId,
            'delete',
            $entityType,
            $entityId,
            $description,
            $data,
            null,
            'warning',
            'data'
        );
    }

    public static function logLogin(int $userId, bool $success = true): void
    {
        self::getService()->log(
            $userId,
            $success ? 'login_success' : 'login_failed',
            'user',
            $userId,
            $success ? 'Benutzer erfolgreich angemeldet' : 'Login-Versuch fehlgeschlagen',
            null,
            null,
            $success ? 'info' : 'warning',
            'auth'
        );
    }

    public static function logLogout(int $userId): void
    {
        self::getService()->log(
            $userId,
            'logout',
            'user',
            $userId,
            'Benutzer abgemeldet',
            null,
            null,
            'info',
            'auth'
        );
    }

    public static function logSecurityEvent(int $userId, string $description, string $severity = 'warning'): void
    {
        self::getService()->log(
            $userId,
            'security_event',
            'system',
            null,
            $description,
            null,
            null,
            $severity,
            'security'
        );
    }

    public static function logError(int $userId, string $description, array $context = []): void
    {
        self::getService()->log(
            $userId,
            'error',
            'system',
            null,
            $description,
            null,
            $context,
            'error',
            'system'
        );
    }
}
