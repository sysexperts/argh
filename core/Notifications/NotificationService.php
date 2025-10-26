<?php
/**
 * Notification Service
 * 
 * @package SysExperts\BusinessManager\Notifications
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Notifications;

use SysExperts\BusinessManager\Database\Database;

class NotificationService
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Benachrichtigung erstellen
     * 
     * @param int $tenantId Mandanten-ID
     * @param int|null $userId User-ID (null = an alle User des Mandanten)
     * @param string $type Typ (z.B. 'new_module', 'ticket', 'invoice')
     * @param string $title Titel
     * @param string $message Nachricht
     * @param string|null $link Optional: Link zur Aktion
     * @param string|null $icon Optional: Material Icon Name
     */
    public function create(
        int $tenantId,
        ?int $userId,
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?string $icon = null
    ): void {
        if ($userId === null) {
            // An alle User des Mandanten senden
            $users = $this->db->fetchAll(
                'SELECT id FROM users WHERE tenant_id = ? AND is_active = 1',
                [$tenantId]
            );
            
            foreach ($users as $user) {
                $this->createSingle($tenantId, (int)$user['id'], $type, $title, $message, $link, $icon);
            }
        } else {
            // An einzelnen User
            $this->createSingle($tenantId, $userId, $type, $title, $message, $link, $icon);
        }
    }

    /**
     * Einzelne Benachrichtigung erstellen
     */
    private function createSingle(
        int $tenantId,
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $link,
        ?string $icon
    ): void {
        $this->db->insert('bm_notifications', [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'icon' => $icon ?? $this->getDefaultIcon($type),
        ]);
    }

    /**
     * Benachrichtigungen eines Users abrufen
     */
    public function getUserNotifications(int $userId, int $limit = 50, bool $unreadOnly = false): array
    {
        $query = "
            SELECT * FROM bm_notifications
            WHERE user_id = ?
        ";
        
        $params = [$userId];
        
        if ($unreadOnly) {
            $query .= " AND is_read = 0";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($query, $params);
    }

    /**
     * Anzahl ungelesener Benachrichtigungen
     */
    public function getUnreadCount(int $userId): int
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM bm_notifications WHERE user_id = ? AND is_read = 0',
            [$userId]
        );
        
        return (int)($result['count'] ?? 0);
    }

    /**
     * Benachrichtigung als gelesen markieren
     */
    public function markAsRead(int $notificationId): void
    {
        $this->db->update('bm_notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$notificationId]);
    }

    /**
     * Alle Benachrichtigungen eines Users als gelesen markieren
     */
    public function markAllAsRead(int $userId): void
    {
        $this->db->getConnection()->prepare("
            UPDATE bm_notifications 
            SET is_read = 1, read_at = ? 
            WHERE user_id = ? AND is_read = 0
        ")->execute([date('Y-m-d H:i:s'), $userId]);
    }

    /**
     * Benachrichtigung löschen
     */
    public function delete(int $notificationId): void
    {
        $this->db->delete('bm_notifications', 'id = ?', [$notificationId]);
    }

    /**
     * Alte Benachrichtigungen löschen (älter als X Tage)
     */
    public function deleteOld(int $days = 30): int
    {
        $date = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        $stmt = $this->db->getConnection()->prepare("
            DELETE FROM bm_notifications 
            WHERE created_at < ? AND is_read = 1
        ");
        $stmt->execute([$date]);
        
        return $stmt->rowCount();
    }

    /**
     * Standard-Icon für Typ
     */
    private function getDefaultIcon(string $type): string
    {
        $icons = [
            'new_module' => 'extension',
            'ticket' => 'support_agent',
            'invoice' => 'receipt_long',
            'customer' => 'person',
            'system' => 'info',
            'warning' => 'warning',
            'success' => 'check_circle',
            'error' => 'error',
        ];
        
        return $icons[$type] ?? 'notifications';
    }

    /**
     * Benachrichtigung für neues Modul im Marketplace
     */
    public function notifyNewModule(int $tenantId, string $moduleName, string $moduleDescription): void
    {
        $this->create(
            $tenantId,
            null, // An alle User
            'new_module',
            '🎉 Neues Modul verfügbar!',
            "Das Modul \"$moduleName\" ist jetzt im Marketplace verfügbar: $moduleDescription",
            '/marketplace',
            'extension'
        );
    }
}
