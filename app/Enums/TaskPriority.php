<?php

namespace App\Enums;

enum TaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    // for API responses
    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::HIGH => 1,
            self::MEDIUM => 2,
            self::LOW => 3,
        };
    }
}
