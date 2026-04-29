<?php

namespace App\Enums;

enum WashOrderStatus: string
{
    case Queued     = 'queued';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Queued     => 'Queued',
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Queued     => 'badge-info',
            self::InProgress => 'badge-warning',
            self::Completed  => 'badge-success',
            self::Cancelled  => 'badge-error',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Queued     => 'info',
            self::InProgress => 'warning',
            self::Completed  => 'success',
            self::Cancelled  => 'error',
        };
    }
}
