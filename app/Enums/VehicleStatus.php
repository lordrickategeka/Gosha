<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case ACTIVE = 'active';
    case IN_SHOP = 'in_shop';
    case DECOMMISSIONED = 'decommissioned';
    case SOLD = 'sold';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::IN_SHOP => 'In Shop',
            self::DECOMMISSIONED => 'Decommissioned',
            self::SOLD => 'Sold',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::IN_SHOP => 'yellow',
            self::DECOMMISSIONED => 'gray',
            self::SOLD => 'blue',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }
}
