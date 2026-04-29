<?php

namespace App\Enums;

enum WashOrderPriority: string
{
    case Normal   = 'normal';
    case Priority = 'priority';

    public function label(): string
    {
        return match ($this) {
            self::Normal   => 'Normal',
            self::Priority => 'Priority',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Normal   => 'badge-ghost',
            self::Priority => 'badge-accent',
        };
    }
}
