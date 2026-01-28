<?php

namespace App\Helpers;

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
}
