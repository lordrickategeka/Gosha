<?php

namespace App\Enums;

enum RfqStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';
    case Awarded = 'awarded';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Closed => 'Closed',
            self::Awarded => 'Awarded',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'badge-ghost',
            self::Published => 'badge-info',
            self::Closed => 'badge-warning',
            self::Awarded => 'badge-success',
            self::Cancelled => 'badge-error',
        };
    }
}
