<?php

namespace App\Domains\Inventory\Services;

class LandedCostCalculator
{
    /**
     * Calculate landed cost.
     *
     * @param  array{
     *   supplier_price?: float|int|string|null,
     *   shipping_cost?: float|int|string|null,
     *   duty_cost?: float|int|string|null,
     *   clearing_cost?: float|int|string|null,
     *   margin_amount?: float|int|string|null,
     *   margin_percent?: float|int|string|null
     * }  $data
     * @return array{
     *   base_cost: float,
     *   margin_value: float,
     *   total_landed_cost: float
     * }
     */
    public function calculate(array $data): array
    {
        $supplierPrice = $this->toFloat($data['supplier_price'] ?? 0);
        $shipping = $this->toFloat($data['shipping_cost'] ?? 0);
        $duty = $this->toFloat($data['duty_cost'] ?? 0);
        $clearing = $this->toFloat($data['clearing_cost'] ?? 0);
        $marginAmount = $this->toFloat($data['margin_amount'] ?? 0);
        $marginPercent = $this->toFloat($data['margin_percent'] ?? 0);

        $base = $supplierPrice + $shipping + $duty + $clearing;
        $percentMarginValue = $marginPercent > 0 ? ($base * ($marginPercent / 100)) : 0;
        $marginValue = $marginAmount > 0 ? $marginAmount : $percentMarginValue;
        $total = $base + $marginValue;

        return [
            'base_cost' => round($base, 2),
            'margin_value' => round($marginValue, 2),
            'total_landed_cost' => round($total, 2),
        ];
    }

    private function toFloat(float|int|string|null $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) $value;
    }
}
