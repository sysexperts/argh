<?php
/**
 * Audit-Log Service
 * GoBD-konformes, unveränderliches Logging-System
 * 
 * @package SysExperts\BusinessManager\AuditLog
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\AuditLog;

use SysExperts\BusinessManager\Database\Database;

class AuditLogService
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Protokolliere eine Aktion
     * 
     * @param int $userId User ID
     * @param string $action Aktion (create, update, delete, login, etc.)
     * @param string $entityType Entity-Typ (invoice, customer, user, etc.)
     * @param int|null $entityId Entity ID
     * @param string $description Beschreibung
     * @param array|null $oldValues Alte Werte (für Updates)
     * @param array|null $newValues Neue Werte
     * @param string $severity Schweregrad (info, warning, error, critical)
     * @param string|null $category Kategorie
     */
    public function log(
        int $userId,
        string $action,
        string $entityType,
        ?int $entityId,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $severity = 'info',
        ?string $category = null
    ): void {
        // Hole User-Daten
        $user = $this->db->fetchOne("
            SELECT 
                COALESCE(first_name || ' ' || last_name, email) as name,
                email 
            FROM users 
            WHERE id = ?
        ", [$userId]);
        
        if (!$user) {
            throw new \Exception("User not found: $userId");
        }

        // Request-Daten
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $requestUrl = $_SERVER['REQUEST_URI'] ?? null;

        // Erstelle Log-Entry
        $logData = [
            'user_id' => $userId,
            'user_name' => $user['name'],
            'user_email' => $user['email'],
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'request_method' => $requestMethod,
            'request_url' => $requestUrl,
            'severity' => $severity,
            'category' => $category,
        ];

        // Berechne Checksumme für Integrität
        $logData['checksum'] = $this->calculateChecksum($logData);

        // Speichere Log (INSERT ONLY - niemals UPDATE/DELETE!)
        $this->db->insert('bm_audit_logs', $logData);
    }

    /**
     * Berechne Checksumme für Log-Integrität
     */
    private function calculateChecksum(array $data): string
    {
        // Sortiere Keys für konsistente Checksumme
        ksort($data);
        
        // Erstelle String aus allen Werten
        $string = implode('|', array_values($data));
        
        // SHA-256 Hash
        return hash('sha256', $string);
    }

    /**
     * Hole Audit-Logs mit Filtern
     */
    public function getLogs(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $where[] = 'action = ?';
            $params[] = $filters['action'];
        }

        if (!empty($filters['entity_type'])) {
            $where[] = 'entity_type = ?';
            $params[] = $filters['entity_type'];
        }

        if (!empty($filters['entity_id'])) {
            $where[] = 'entity_id = ?';
            $params[] = $filters['entity_id'];
        }

        if (!empty($filters['severity'])) {
            $where[] = 'severity = ?';
            $params[] = $filters['severity'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= ?';
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT * FROM bm_audit_logs 
            $whereClause 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Zähle Logs
     */
    public function countLogs(array $filters = []): int
    {
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['entity_type'])) {
            $where[] = 'entity_type = ?';
            $params[] = $filters['entity_type'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM bm_audit_logs $whereClause", $params);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Verifiziere Log-Integrität
     */
    public function verifyIntegrity(int $logId): bool
    {
        $log = $this->db->fetchOne("SELECT * FROM bm_audit_logs WHERE id = ?", [$logId]);
        
        if (!$log) {
            return false;
        }

        $storedChecksum = $log['checksum'];
        unset($log['checksum']);
        unset($log['id']);

        $calculatedChecksum = $this->calculateChecksum($log);

        return $storedChecksum === $calculatedChecksum;
    }

    /**
     * Exportiere Logs für GoBD-Archivierung
     */
    public function exportLogs(array $filters = []): string
    {
        $logs = $this->getLogs($filters, 10000, 0);
        
        $csv = "ID;Datum;Benutzer;Aktion;Entity;Beschreibung;Checksumme\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d;%s;%s;%s;%s;%s;%s\n",
                $log['id'],
                $log['created_at'],
                $log['user_name'],
                $log['action'],
                $log['entity_type'],
                str_replace(["\n", "\r", ";"], [" ", " ", ","], $log['description']),
                $log['checksum']
            );
        }
        
        return $csv;
    }
}
