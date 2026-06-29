<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Unpaid => 'badge-error',
            self::Partial => 'badge-warning',
            self::Paid => 'badge-success',
        };
    }
}
