<?php
declare(strict_types=1);

namespace SysExperts\BusinessManager\TimeTracking;

use PDO;
use DateTime;

class TimeTrackingService
{
    private PDO $db;
    private int $tenantId;
    private const REGULAR_HOURS = 8.0;
    private const MAX_DAILY_HOURS = 10.0;
    private const MIN_REST_HOURS = 11.0;

    public function __construct(PDO $db, int $tenantId = 1)
    {
        $this->db = $db;
        $this->tenantId = $tenantId;
    }

    public function startWork(int $userId, ?string $notes = null): TimeEntry
    {
        $date = date('Y-m-d');
        $startTime = date('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            INSERT INTO time_entries (user_id, tenant_id, date, start_time, status, notes)
            VALUES (?, ?, ?, ?, 'active', ?)
        ");
        $stmt->execute([$userId, $this->tenantId, $date, $startTime, $notes]);

        $entryId = (int)$this->db->lastInsertId();
        $this->logAudit($entryId, $userId, 'start_work', null, ['start_time' => $startTime]);

        return $this->getEntryById($entryId);
    }

    public function endWork(int $entryId, int $userId): TimeEntry
    {
        $entry = $this->getEntryById($entryId);
        $endTime = date('Y-m-d H:i:s');

        $totalMinutes = $this->calculateWorkMinutes($entry->getStartTime(), $endTime, $entryId);
        $totalHours = round($totalMinutes / 60, 2);
        $overtimeHours = max(0, $totalHours - self::REGULAR_HOURS);

        $stmt = $this->db->prepare("
            UPDATE time_entries 
            SET end_time = ?, total_hours = ?, overtime_hours = ?, status = 'completed', updated_at = ?
            WHERE id = ? AND tenant_id = ?
        ");
        $stmt->execute([$endTime, $totalHours, $overtimeHours, date('Y-m-d H:i:s'), $entryId, $this->tenantId]);

        $this->logAudit($entryId, $userId, 'end_work', 
            ['end_time' => null], 
            ['end_time' => $endTime, 'total_hours' => $totalHours]
        );

        return $this->getEntryById($entryId);
    }

    public function startBreak(int $entryId, int $userId, string $breakType = 'regular'): int
    {
        $startTime = date('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            INSERT INTO time_breaks (time_entry_id, start_time, break_type)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$entryId, $startTime, $breakType]);

        $breakId = (int)$this->db->lastInsertId();

        $stmt = $this->db->prepare("UPDATE time_entries SET status = 'paused', updated_at = ? WHERE id = ?");
        $stmt->execute([date('Y-m-d H:i:s'), $entryId]);

        $this->logAudit($entryId, $userId, 'start_break', null, ['break_start' => $startTime]);

        return $breakId;
    }

    public function endBreak(int $breakId, int $entryId, int $userId): void
    {
        $endTime = date('Y-m-d H:i:s');

        $stmt = $this->db->prepare("SELECT start_time FROM time_breaks WHERE id = ?");
        $stmt->execute([$breakId]);
        $break = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($break) {
            $duration = $this->calculateMinutesBetween($break['start_time'], $endTime);

            $stmt = $this->db->prepare("
                UPDATE time_breaks 
                SET end_time = ?, duration_minutes = ?
                WHERE id = ?
            ");
            $stmt->execute([$endTime, $duration, $breakId]);
        }

        $stmt = $this->db->prepare("UPDATE time_entries SET status = 'active', updated_at = ? WHERE id = ?");
        $stmt->execute([date('Y-m-d H:i:s'), $entryId]);

        $this->logAudit($entryId, $userId, 'end_break', null, ['break_end' => $endTime]);
    }

    public function getActiveEntry(int $userId): ?TimeEntry
    {
        $stmt = $this->db->prepare("
            SELECT * FROM time_entries 
            WHERE user_id = ? AND tenant_id = ? AND status IN ('active', 'paused')
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$userId, $this->tenantId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $entry = new TimeEntry($data);
        $entry->setBreaks($this->getBreaksForEntry($entry->getId()));
        return $entry;
    }

    public function getEntriesByUser(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT * FROM time_entries WHERE user_id = ? AND tenant_id = ?";
        $params = [$userId, $this->tenantId];

        if ($startDate) {
            $sql .= " AND date >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND date <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY date DESC, start_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($data) {
            $entry = new TimeEntry($data);
            $entry->setBreaks($this->getBreaksForEntry($entry->getId()));
            return $entry;
        }, $entries);
    }

    public function getAllEntries(?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT * FROM time_entries WHERE tenant_id = ?";
        $params = [$this->tenantId];

        if ($startDate) {
            $sql .= " AND date >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND date <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY date DESC, start_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($data) {
            $entry = new TimeEntry($data);
            $entry->setBreaks($this->getBreaksForEntry($entry->getId()));
            return $entry;
        }, $entries);
    }

    public function getEntryById(int $id): TimeEntry
    {
        $stmt = $this->db->prepare("SELECT * FROM time_entries WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$id, $this->tenantId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new \Exception('Zeiteintrag nicht gefunden');
        }

        $entry = new TimeEntry($data);
        $entry->setBreaks($this->getBreaksForEntry($id));
        return $entry;
    }

    public function updateEntry(int $id, int $userId, array $data): TimeEntry
    {
        $entry = $this->getEntryById($id);
        $oldValues = $entry->toArray();

        $fields = [];
        $values = [];

        if (isset($data['start_time'])) {
            $fields[] = 'start_time = ?';
            $values[] = $data['start_time'];
        }
        if (isset($data['end_time'])) {
            $fields[] = 'end_time = ?';
            $values[] = $data['end_time'];
        }
        if (isset($data['notes'])) {
            $fields[] = 'notes = ?';
            $values[] = $data['notes'];
        }

        if (!empty($fields)) {
            $fields[] = 'updated_at = ?';
            $values[] = date('Y-m-d H:i:s');
            $values[] = $id;
            $values[] = $this->tenantId;

            $sql = "UPDATE time_entries SET " . implode(', ', $fields) . " WHERE id = ? AND tenant_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            $this->logAudit($id, $userId, 'update_entry', $oldValues, $data);
        }

        return $this->getEntryById($id);
    }

    public function deleteEntry(int $id, int $userId): bool
    {
        $entry = $this->getEntryById($id);
        $this->logAudit($id, $userId, 'delete_entry', $entry->toArray(), null);

        $stmt = $this->db->prepare("DELETE FROM time_entries WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $this->tenantId]);
    }

    public function checkViolations(TimeEntry $entry): array
    {
        $violations = [];

        if ($entry->getTotalHours() && $entry->getTotalHours() > self::MAX_DAILY_HOURS) {
            $violations[] = [
                'type' => 'max_hours_exceeded',
                'message' => 'Maximale Arbeitszeit von 10 Stunden überschritten',
                'severity' => 'error'
            ];
        }

        if ($entry->getTotalHours() && $entry->getTotalHours() > self::REGULAR_HOURS) {
            $violations[] = [
                'type' => 'overtime',
                'message' => sprintf('Überstunden: %.2f Stunden', $entry->getOvertimeHours()),
                'severity' => 'warning'
            ];
        }

        $previousEntry = $this->getPreviousEntry($entry->getUserId(), $entry->getDate());
        if ($previousEntry && $previousEntry->getEndTime()) {
            $restHours = $this->calculateRestHours($previousEntry->getEndTime(), $entry->getStartTime());
            if ($restHours < self::MIN_REST_HOURS) {
                $violations[] = [
                    'type' => 'insufficient_rest',
                    'message' => sprintf('Ruhezeit unter 11 Stunden (%.1f Stunden)', $restHours),
                    'severity' => 'error'
                ];
            }
        }

        return $violations;
    }

    public function getWeeklySummary(int $userId, string $weekStart): array
    {
        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));
        $entries = $this->getEntriesByUser($userId, $weekStart, $weekEnd);

        $totalHours = 0;
        $totalOvertime = 0;
        $daysWorked = 0;

        foreach ($entries as $entry) {
            if ($entry->getTotalHours()) {
                $totalHours += $entry->getTotalHours();
                $totalOvertime += $entry->getOvertimeHours();
                $daysWorked++;
            }
        }

        return [
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'total_hours' => round($totalHours, 2),
            'total_overtime' => round($totalOvertime, 2),
            'days_worked' => $daysWorked,
            'average_hours' => $daysWorked > 0 ? round($totalHours / $daysWorked, 2) : 0,
            'entries' => $entries
        ];
    }

    private function getBreaksForEntry(int $entryId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM time_breaks WHERE time_entry_id = ? ORDER BY start_time");
        $stmt->execute([$entryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function calculateWorkMinutes(string $startTime, string $endTime, int $entryId): int
    {
        $totalMinutes = $this->calculateMinutesBetween($startTime, $endTime);

        $stmt = $this->db->prepare("
            SELECT SUM(duration_minutes) as total_break 
            FROM time_breaks 
            WHERE time_entry_id = ? AND duration_minutes IS NOT NULL
        ");
        $stmt->execute([$entryId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $breakMinutes = (int)($result['total_break'] ?? 0);

        return max(0, $totalMinutes - $breakMinutes);
    }

    private function calculateMinutesBetween(string $start, string $end): int
    {
        $startDt = new DateTime($start);
        $endDt = new DateTime($end);
        $diff = $endDt->getTimestamp() - $startDt->getTimestamp();
        return (int)($diff / 60);
    }

    private function calculateRestHours(string $previousEnd, string $currentStart): float
    {
        $minutes = $this->calculateMinutesBetween($previousEnd, $currentStart);
        return round($minutes / 60, 1);
    }

    private function getPreviousEntry(int $userId, string $date): ?TimeEntry
    {
        $stmt = $this->db->prepare("
            SELECT * FROM time_entries 
            WHERE user_id = ? AND tenant_id = ? AND date < ? AND status = 'completed'
            ORDER BY date DESC, end_time DESC LIMIT 1
        ");
        $stmt->execute([$userId, $this->tenantId, $date]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new TimeEntry($data) : null;
    }

    private function logAudit(int $entryId, int $userId, string $action, ?array $oldValues, ?array $newValues): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO time_audit_log (time_entry_id, user_id, action, old_values, new_values, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $entryId,
            $userId,
            $action,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}
