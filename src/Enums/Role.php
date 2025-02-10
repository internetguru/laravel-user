<?php

namespace InternetGuru\LaravelUser\Enums;

enum Role: string
{
    case CUSTOMER = 'customer';
    case OPERATOR = 'operator';
    case AUDITOR = 'auditor';
    case MANAGER = 'manager';
    case ADMIN = 'admin';

    public function level(): int
    {
        return match ($this) {
            self::CUSTOMER => 10,
            self::OPERATOR => 20,
            self::AUDITOR => 30,
            self::MANAGER => 40,
            self::ADMIN => 50,
        };
    }

    public function icon(): string
    {
        return match (true) {
            $this->level() >= 50 => 'fa-user-shield',
            $this->level() >= 40 => 'fa-user-tie',
            $this->level() >= 20 => 'fa-user-nurse',
            default => 'fa-user',
        };
    }
}
