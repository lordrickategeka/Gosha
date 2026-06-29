<?php

namespace App\Enums;

enum TransmissionType: string
{
    case MANUAL = 'manual';
    case AUTOMATIC = 'automatic';
    case CVT = 'cvt';
    case DUAL_CLUTCH = 'dual_clutch';

    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'Manual',
            self::AUTOMATIC => 'Automatic',
            self::CVT => 'CVT (Continuously Variable Transmission)',
            self::DUAL_CLUTCH => 'Dual-Clutch',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }
}
