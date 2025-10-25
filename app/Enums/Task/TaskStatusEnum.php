<?php

namespace App\Enums\Task;

enum TaskStatusEnum: int
{
    case Pending = 1;
    case InProgress = 2;
    case Done = 3;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'InProgress',
            self::Done => 'Done',
        };
    }
}
