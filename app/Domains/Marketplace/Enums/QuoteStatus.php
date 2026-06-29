<?php

namespace App\Domains\Marketplace\Enums;

enum QuoteStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Awarded = 'awarded';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'badge-ghost',
            self::Submitted => 'badge-info',
            self::Awarded => 'badge-success',
            self::Rejected, self::Expired, self::Withdrawn => 'badge-error',
        };
    }
}
