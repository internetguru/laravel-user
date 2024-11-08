<?php

namespace InternetGuru\LaravelUser\Enums;

enum Role: string
{
    case SPECTATOR = 'spectator';
    case OPERATOR = 'operator';
    case MANAGER = 'manager';
    case ADMIN = 'admin';

    public function level(): int
    {
        return match ($this) {
            self::SPECTATOR => 10,
            self::OPERATOR => 20,
            self::MANAGER => 30,
            self::ADMIN => 40,
        };
    }
}
