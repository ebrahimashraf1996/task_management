<?php

namespace App\Enums\Task;

enum TaskPriorityEnum: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
        };
    }
}
