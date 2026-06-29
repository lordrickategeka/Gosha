<?php

namespace App\Domains\Inventory\Services;

use App\Domains\Inventory\Models\SupplierPart;
use App\Domains\Operations\Models\WorkOrderItem;
use Illuminate\Support\Collection;

class PartRecommendationService
{
    public function __construct(
        private readonly LandedCostCalculator $landedCostCalculator = new LandedCostCalculator(),
        private readonly FitmentConfidenceScorer $fitmentConfidenceScorer = new FitmentConfidenceScorer(),
        private readonly InterchangeResolver $interchangeResolver = new InterchangeResolver(),
    ) {
    }

    /**
     * Build ranked recommendations for a work order item.
     *
     * @return array{
     *   candidates: array<int, array<string,mixed>>,
     *   recommended: ?array<string,mixed>
     * }
     */
    public function recommend(WorkOrderItem $workOrderItem, ?int $requestedPartOemNumberId = null): array
    {
        $oemIds = collect();

        if ($requestedPartOemNumberId) {
            $oemIds->push($requestedPartOemNumberId);

            $interchangeIds = $this->interchangeResolver
                ->resolve($requestedPartOemNumberId)
                ->pluck('oem_number_id');

            $oemIds = $oemIds->merge($interchangeIds)->unique()->values();
        }

        $supplierParts = $this->querySupplierCandidates($workOrderItem, $oemIds);

        $candidates = $supplierParts->map(function (SupplierPart $supplierPart) use ($workOrderItem, $requestedPartOemNumberId) {
            $cost = $this->landedCostCalculator->calculate([
                'supplier_price' => $supplierPart->supplier_price,
                'shipping_cost' => $supplierPart->shipping_cost,
                'duty_cost' => $supplierPart->duty_cost,
                'clearing_cost' => $supplierPart->clearing_cost,
                'margin_amount' => $supplierPart->margin_amount,
                'margin_percent' => $supplierPart->margin_percent,
            ]);

            $fitment = $this->fitmentConfidenceScorer->score(
                $workOrderItem,
                $supplierPart,
                $requestedPartOemNumberId
            );

            return [
                'supplier_part_id' => $supplierPart->id,
                'supplier_id' => $supplierPart->supplier_id,
                'supplier_name' => $supplierPart->supplier?->name,
                'supplier_link' => $supplierPart->supplier_link,
                'supplier_part_number' => $supplierPart->supplier_part_number,
                'part_oem_number_id' => $supplierPart->part_oem_number_id,
                'is_local' => (bool) $supplierPart->is_local,
                'availability' => $supplierPart->availability,
                'lead_time_days' => $supplierPart->lead_time_days,
                'warranty_text' => $supplierPart->warranty_text,
                'supplier_price' => (float) ($supplierPart->supplier_price ?? 0),
                'base_cost' => $cost['base_cost'],
                'margin_value' => $cost['margin_value'],
                'total_landed_cost' => $cost['total_landed_cost'],
                'confidence_score' => $fitment['score'],
                'reason_codes' => $fitment['reasons'],
            ];
        })->sortBy([
            ['confidence_score', 'desc'],
            ['total_landed_cost', 'asc'],
        ])->values();

        return [
            'candidates' => $candidates->all(),
            'recommended' => $candidates->first(),
        ];
    }

    private function querySupplierCandidates(WorkOrderItem $workOrderItem, Collection $oemIds): Collection
    {
        $query = SupplierPart::query()
            ->with('supplier')
            ->where('is_active', true);

        if ($oemIds->isNotEmpty()) {
            $query->whereIn('part_oem_number_id', $oemIds->all());
        } elseif ($workOrderItem->inventory_item_id) {
            // Fallback heuristic in v1 manual flow:
            // try matching supplier_part_name by work item description when OEM is unknown
            $description = trim((string) $workOrderItem->description);
            if ($description !== '') {
                $query->where('supplier_part_name', 'like', '%' . $description . '%');
            }
        }

        return $query->limit(50)->get();
    }
}
