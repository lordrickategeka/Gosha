<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class SalesReportExport implements FromCollection
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }
}
