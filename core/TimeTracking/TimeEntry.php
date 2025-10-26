<?php
declare(strict_types=1);

namespace SysExperts\BusinessManager\TimeTracking;

class TimeEntry
{
    private int $id;
    private int $userId;
    private int $tenantId;
    private string $date;
    private string $startTime;
    private ?string $endTime;
    private ?float $totalHours;
    private float $overtimeHours;
    private string $status;
    private ?string $notes;
    private array $breaks = [];
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->userId = (int)$data['user_id'];
        $this->tenantId = (int)$data['tenant_id'];
        $this->date = $data['date'];
        $this->startTime = $data['start_time'];
        $this->endTime = $data['end_time'] ?? null;
        $this->totalHours = isset($data['total_hours']) ? (float)$data['total_hours'] : null;
        $this->overtimeHours = (float)($data['overtime_hours'] ?? 0);
        $this->status = $data['status'];
        $this->notes = $data['notes'] ?? null;
        $this->createdAt = $data['created_at'];
        $this->updatedAt = $data['updated_at'];
    }

    public function getId(): int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getDate(): string { return $this->date; }
    public function getStartTime(): string { return $this->startTime; }
    public function getEndTime(): ?string { return $this->endTime; }
    public function getTotalHours(): ?float { return $this->totalHours; }
    public function getOvertimeHours(): float { return $this->overtimeHours; }
    public function getStatus(): string { return $this->status; }
    public function getNotes(): ?string { return $this->notes; }
    public function getBreaks(): array { return $this->breaks; }
    public function setBreaks(array $breaks): void { $this->breaks = $breaks; }

    public function isActive(): bool { return $this->status === 'active'; }
    public function isPaused(): bool { return $this->status === 'paused'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'tenant_id' => $this->tenantId,
            'date' => $this->date,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'total_hours' => $this->totalHours,
            'overtime_hours' => $this->overtimeHours,
            'status' => $this->status,
            'notes' => $this->notes,
            'breaks' => $this->breaks,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
