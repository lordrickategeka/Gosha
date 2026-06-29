<?php

namespace App\Enums;

enum VendorType: string
{
    case Garage = 'garage';
    case CarWash = 'car_wash';
    case Supplier = 'supplier';
    case Hybrid = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::Garage => 'Garage',
            self::CarWash => 'Car Wash',
            self::Supplier => 'Supplier',
            self::Hybrid => 'Hybrid (Garage + Supplier)',
        };
    }

    public function canSell(): bool
    {
        return in_array($this, [self::Supplier, self::Hybrid], true);
    }

    public function canBuy(): bool
    {
        return in_array($this, [self::Garage, self::CarWash, self::Hybrid], true);
    }
}
