<?php

namespace App\Enums;

enum FuelType: string
{
    case GASOLINE = 'gasoline';
    case DIESEL = 'diesel';
    case FLEX_FUEL = 'flex_fuel';
    case HEV = 'hev';
    case PHEV = 'phev';
    case BEV = 'bev';

    public function label(): string
    {
        return match ($this) {
            self::GASOLINE => 'Gasoline',
            self::DIESEL => 'Diesel',
            self::FLEX_FUEL => 'Flex Fuel',
            self::HEV => 'Hybrid (HEV)',
            self::PHEV => 'Plug-in Hybrid (PHEV)',
            self::BEV => 'Battery Electric (BEV)',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }
}
