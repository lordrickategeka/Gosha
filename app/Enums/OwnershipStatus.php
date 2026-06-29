<?php

namespace App\Enums;

enum OwnershipStatus: string
{
    case OWNED = 'owned';
    case LEASED = 'leased';
    case FINANCED = 'financed';
    case CUSTOMER_OWNED = 'customer_owned';

    public function label(): string
    {
        return match ($this) {
            self::OWNED => 'Owned',
            self::LEASED => 'Leased',
            self::FINANCED => 'Financed',
            self::CUSTOMER_OWNED => 'Customer Owned',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }
}
