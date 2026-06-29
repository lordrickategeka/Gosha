<?php

namespace App\Enums;

enum OdometerSource: string
{
    case MANUAL_ENTRY = 'manual_entry';
    case OBD_DONGLE = 'obd_dongle';
    case DRIVER_APP = 'driver_app';

    public function label(): string
    {
        return match ($this) {
            self::MANUAL_ENTRY => 'Manual Entry',
            self::OBD_DONGLE => 'OBD Dongle',
            self::DRIVER_APP => 'Driver App',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }
}
