<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class QuotationPdfController extends Controller
{
    public function download(Quotation $quotation)
    {
        Gate::authorize('view_quotations');

        $quotation->load([
            'items.inventoryItem',
            'items.supplier',
            'workOrder.vehicle',
            'workOrder.branch',
            'customer',
            'createdBy',
        ]);

        $pdf = Pdf::loadView('pdf.quotation', compact('quotation'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Quotation-' . $quotation->quotation_number . '.pdf');
    }
}
