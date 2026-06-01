<?php

namespace App\Services\PartsIntelligence;

use App\Models\PartInterchange;
use App\Models\PartOemNumber;
use Illuminate\Support\Collection;

class InterchangeResolver
{
    /**
     * Resolve interchange candidates for a given OEM number id.
     *
     * @return Collection<int, array{
     *   oem_number_id:int,
     *   oem_part_number:string,
     *   interchange_type:string,
     *   market_region:?string,
     *   year_from:?int,
     *   year_to:?int
     * }>
     */
    public function resolve(int $partOemNumberId): Collection
    {
        $base = PartOemNumber::find($partOemNumberId);
        if (!$base) {
            return collect();
        }

        $rows = PartInterchange::query()
            ->with([
                'fromPartOemNumber:id,oem_part_number',
                'toPartOemNumber:id,oem_part_number',
            ])
            ->where(function ($q) use ($partOemNumberId) {
                $q->where('from_part_oem_number_id', $partOemNumberId)
                    ->orWhere('to_part_oem_number_id', $partOemNumberId);
            })
            ->where('is_active', true)
            ->get();

        return $rows->map(function (PartInterchange $row) use ($partOemNumberId) {
            $isFrom = (int) $row->from_part_oem_number_id === $partOemNumberId;
            $target = $isFrom ? $row->toPartOemNumber : $row->fromPartOemNumber;

            return [
                'oem_number_id' => (int) ($target?->id ?? 0),
                'oem_part_number' => (string) ($target?->oem_part_number ?? ''),
                'interchange_type' => (string) $row->interchange_type,
                'market_region' => $row->market_region,
                'year_from' => $row->year_from,
                'year_to' => $row->year_to,
            ];
        })->filter(fn (array $item) => $item['oem_number_id'] > 0)->values();
    }
}
