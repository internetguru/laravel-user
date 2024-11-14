<?php

namespace InternetGuru\LaravelUser\Enums;

enum Role: string
{
    case PENDING = 'pending';
    case SPECTATOR = 'spectator';
    case OPERATOR = 'operator';
    case MANAGER = 'manager';
    case ADMIN = 'admin';

    public function level(): int
    {
        return match ($this) {
            self::PENDING => 1,
            self::SPECTATOR => 10,
            self::OPERATOR => 20,
            self::MANAGER => 30,
            self::ADMIN => 40,
        };
    }
}
