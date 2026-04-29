<?php

namespace App\Enums;

enum WashBayStatus: string
{
    case Available  = 'available';
    case Occupied   = 'occupied';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Available   => 'Available',
            self::Occupied    => 'Occupied',
            self::Maintenance => 'Maintenance',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Available   => 'badge-success',
            self::Occupied    => 'badge-warning',
            self::Maintenance => 'badge-error',
        };
    }

    public function borderClass(): string
    {
        return match ($this) {
            self::Available   => 'border-success',
            self::Occupied    => 'border-warning',
            self::Maintenance => 'border-error',
        };
    }
}
