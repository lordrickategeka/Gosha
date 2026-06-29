<?php

namespace App\Enums;

enum DrivetrainType: string
{
    case FWD = 'fwd';
    case RWD = 'rwd';
    case AWD = 'awd';
    case WHEEL_DRIVE_4WD = '4wd';

    public function label(): string
    {
        return match ($this) {
            self::FWD => 'Front-Wheel Drive (FWD)',
            self::RWD => 'Rear-Wheel Drive (RWD)',
            self::AWD => 'All-Wheel Drive (AWD)',
            self::WHEEL_DRIVE_4WD => 'Four-Wheel Drive (4WD)',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }
}
