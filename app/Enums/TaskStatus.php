<?php

namespace App\Enums;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case OVERDUE = 'overdue';

    // for API responses
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::OVERDUE => 'Overdue',
        };
    }

    // for logic enforcement (like blocking status jump to Completed directly)
    public function canTransitionTo(TaskStatus $newStatus): bool
    {
        return match ($this) {
            self::PENDING => in_array($newStatus, [self::IN_PROGRESS, self::OVERDUE]),
            self::IN_PROGRESS => in_array($newStatus, [self::COMPLETED, self::OVERDUE]),
            self::COMPLETED => false,
            self::OVERDUE => in_array($newStatus, [self::IN_PROGRESS, self::COMPLETED]),
        };
    }
}
