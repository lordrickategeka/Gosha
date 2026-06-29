<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Fulfilling = 'fulfilling';
    case Received = 'received';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'badge-ghost',
            self::Sent => 'badge-info',
            self::Accepted, self::Fulfilling => 'badge-warning',
            self::Received => 'badge-accent',
            self::Completed => 'badge-success',
            self::Cancelled => 'badge-error',
        };
    }

    /** Statuses at/after which stock has begun arriving. */
    public function isReceivable(): bool
    {
        return in_array($this, [self::Accepted, self::Fulfilling, self::Received], true);
    }
}
