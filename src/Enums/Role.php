<?php

namespace InternetGuru\LaravelUser\Enums;

use InternetGuru\LaravelCommon\Contracts\HasLabel;
use InternetGuru\LaravelCommon\Traits\RendersLabel;

enum Role: string implements HasLabel
{
    use RendersLabel;

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
            $this->level() >= 50 => 'fa-user-gear',
            $this->level() >= 40 => 'fa-user-tie',
            $this->level() >= 30 => 'fa-user-shield',
            $this->level() >= 20 => 'fa-user-nurse',
            default => 'fa-user',
        };
    }

    public function translation(): string
    {
        return match ($this) {
            self::CUSTOMER => __('ig-user::user.roles.customer'),
            self::OPERATOR => __('ig-user::user.roles.operator'),
            self::AUDITOR => __('ig-user::user.roles.auditor'),
            self::MANAGER => __('ig-user::user.roles.manager'),
            self::ADMIN => __('ig-user::user.roles.admin'),
        };
    }

    public function label(): string
    {
        return $this->translation();
    }

    /**
     * Coloured by how far a role reaches rather than by the role itself, so the
     * accounts that can do the most stand out in a list of them.
     */
    public function variant(): ?string
    {
        return match (true) {
            $this->level() >= 50 => 'danger',
            $this->level() >= 40 => 'warning',
            $this->level() >= 30 => 'info',
            $this->level() >= 20 => 'primary',
            default => 'secondary',
        };
    }

    /** A role is already recognised by its icon, which stands in for the dot. */
    protected function labelIcon(): string
    {
        return 'fa-solid fa-fw ' . $this->icon();
    }
}
