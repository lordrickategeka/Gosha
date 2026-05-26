<?php

namespace App\Helpers;

use App\Models\InventoryCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class NumberGeneratorHelper
{
    /**
     * Generate a unique job card number in the format: JC-YYYYMMDD-XXXX
     *
     * @return string
     */
    public static function generateJobCardNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'JC-' . $date . '-';
        $last = DB::table('job_cards')
            ->where('job_card_number', 'like', $prefix . '%')
            ->orderByDesc('job_card_number')
            ->value('job_card_number');

        if ($last) {
            $lastNumber = (int) substr($last, -4);
            $next = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }

        return $prefix . $next;
    }

    public static function generateInventoryCategoryCode(string $name, string $type, int $vendorId, ?int $excludeId = null): string
    {
        $typePrefix = match ($type) {
            'service_parts' => 'SP',
            'wash_supplies' => 'WS',
            'consumables' => 'CN',
            'tools' => 'TL',
            default => 'IV',
        };

        $normalized = strtoupper(preg_replace('/[^A-Z0-9]+/', '', Str::ascii($name)) ?: '');
        $base = substr($normalized ?: 'CAT', 0, 4);
        $candidate = $typePrefix . '-' . $base;
        $suffix = 2;

        while (DB::table('inventory_categories')
            ->where('vendor_id', $vendorId)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->where('code', $candidate)
            ->exists()) {
            $candidate = $typePrefix . '-' . $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    public static function ensureInventoryCategoryCode(InventoryCategory $category): string
    {
        $code = $category->code ?: static::generateInventoryCategoryCode(
            $category->name,
            $category->type,
            $category->vendor_id,
            $category->id,
        );

        if (! $category->code && $category->exists) {
            $category->forceFill(['code' => $code])->saveQuietly();
        }

        return $code;
    }

    public static function generateInventorySku(string $categoryCode, int $vendorId): string
    {
        $prefix = strtoupper(trim($categoryCode));

        $lastSku = DB::table('inventory_items')
            ->where('vendor_id', $vendorId)
            ->where('sku', 'like', $prefix . '-%')
            ->orderByDesc('sku')
            ->value('sku');

        $nextNumber = 1;
        if ($lastSku && preg_match('/-(\d+)$/', $lastSku, $matches) === 1) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return $prefix . '-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
