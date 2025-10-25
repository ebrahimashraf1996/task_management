<?php

namespace App\Enums\User;

enum UserRoleEnum: string
{
    case Admin = 'admin';
    case User = 'user';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::User => 'User',
        };
    }
}
