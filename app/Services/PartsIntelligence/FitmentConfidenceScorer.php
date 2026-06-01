<?php

namespace App\Services\PartsIntelligence;

use App\Models\PartInstallationHistory;
use App\Models\SupplierPart;
use App\Models\Vehicle;
use App\Models\WorkOrderItem;

class FitmentConfidenceScorer
{
    /**
     * Score confidence (0-100) and return reason codes.
     *
     * @return array{score:int,reasons:array<int,string>}
     */
    public function score(
        WorkOrderItem $workOrderItem,
        ?SupplierPart $supplierPart = null,
        ?int $requestedPartOemNumberId = null
    ): array {
        $vehicle = $workOrderItem->workOrder?->vehicle;
        $vehicleProfile = $vehicle?->profile;

        $score = 0;
        $reasons = [];

        // 1) Exact OEM match (40)
        if ($requestedPartOemNumberId && $supplierPart?->part_oem_number_id === $requestedPartOemNumberId) {
            $score += 40;
            $reasons[] = 'exact_oem_match';
        }

        // 2) VIN/chassis match proxy via profile presence and chassis_code alignment (15)
        if ($vehicleProfile && !empty($vehicleProfile->chassis_code)) {
            $score += 15;
            $reasons[] = 'vehicle_profile_chassis_available';
        }

        // 3) Engine/transmission match via fitment profile richness (15)
        if ($vehicleProfile && !empty($vehicleProfile->engine_code) && !empty($vehicleProfile->transmission)) {
            $score += 15;
            $reasons[] = 'engine_transmission_context_available';
        }

        // 4) Year range confidence proxy from known vehicle year (10)
        if ($vehicle && !empty($vehicle->year)) {
            $score += 10;
            $reasons[] = 'vehicle_year_available';
        }

        // 5) Installation success history (15)
        $historyScore = $this->scoreHistory($vehicle, $supplierPart);
        if ($historyScore > 0) {
            $score += $historyScore;
            $reasons[] = 'historical_fitment_success';
        }

        // 6) Supplier reliability (5)
        $supplierScore = $this->scoreSupplierReliability($supplierPart);
        if ($supplierScore > 0) {
            $score += $supplierScore;
            $reasons[] = 'supplier_reliability';
        }

        return [
            'score' => min(100, max(0, (int) round($score))),
            'reasons' => $reasons,
        ];
    }

    private function scoreHistory(?Vehicle $vehicle, ?SupplierPart $supplierPart): int
    {
        if (!$vehicle || !$supplierPart) {
            return 0;
        }

        $query = PartInstallationHistory::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('supplier_part_id', $supplierPart->id);

        $total = (int) $query->count();
        if ($total === 0) {
            return 0;
        }

        $success = (int) (clone $query)->where('fit_status', 'fitted_ok')->count();
        $ratio = $success / max(1, $total);

        return (int) round(15 * $ratio);
    }

    private function scoreSupplierReliability(?SupplierPart $supplierPart): int
    {
        if (!$supplierPart) {
            return 0;
        }

        $query = PartInstallationHistory::query()
            ->where('supplier_part_id', $supplierPart->id);

        $total = (int) $query->count();
        if ($total < 3) {
            return 0;
        }

        $success = (int) (clone $query)->where('fit_status', 'fitted_ok')->count();
        $ratio = $success / max(1, $total);

        return (int) round(5 * $ratio);
    }
}
