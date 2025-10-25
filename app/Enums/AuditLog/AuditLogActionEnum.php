<?php

namespace App\Enums\AuditLog;

enum AuditLogActionEnum: int
{
    case Create = 1;
    case Update = 2;
    case Delete = 3;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Create => 'Create',
            self::Update => 'Update',
            self::Delete => 'Delete',
        };
    }
}
