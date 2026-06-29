<?php

namespace App\Domains\Operations\Enums;

enum WashBayType: string
{
    case Basic       = 'basic';
    case Standard    = 'standard';
    case Premium     = 'premium';
    case FullService = 'full_service';
    case Detailing   = 'detailing';
    case Automated   = 'automated';

    public function label(): string
    {
        return match ($this) {
            self::Basic       => 'Basic',
            self::Standard    => 'Standard',
            self::Premium     => 'Premium',
            self::FullService => 'Full Service',
            self::Detailing   => 'Detailing',
            self::Automated   => 'Automated',
        };
    }
}
