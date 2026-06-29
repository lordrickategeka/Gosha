<?php

namespace App\Http\Controllers;

use App\Domains\Finance\Models\Quotation;
use Illuminate\Http\Request;

class PublicQuotationController extends Controller
{
    /**
     * Show the quotation to the customer (no auth required).
     */
    public function show(string $token)
    {
        $quotation = Quotation::with([
            'items.inventoryItem',
            'items.supplier',
            'workOrder.vehicle',
            'workOrder.customer',
            'customer',
        ])->where('approval_token', $token)->firstOrFail();

        return view('public.quotation', compact('quotation'));
    }

    /**
     * Customer approves the quotation via the shareable link.
     */
    public function approve(string $token)
    {
        $quotation = Quotation::where('approval_token', $token)->firstOrFail();

        if (!$quotation->canBeApproved()) {
            return back()->with('error', 'This quotation can no longer be approved (it may be expired, already approved, or rejected).');
        }

        $quotation->update([
            'status'      => 'approved',
            'approved_at' => now(),
        ]);

        // Sync prices back to work order items
        $workOrder = $quotation->workOrder()->with('items')->first();
        foreach ($quotation->items as $item) {
            $woItem = $workOrder->items->where('description', $item->description)->first();
            if ($woItem) {
                $woItem->update([
                    'unit_price'  => $item->unit_price,
                    'discount'    => $item->discount,
                    'total'       => $item->total,
                    'supplier_id' => $item->supplier_id,
                ]);
            }
        }

        return redirect()->route('quotations.public', $token)
            ->with('success', 'Thank you! Your quotation has been approved. Our team will contact you shortly.');
    }

    /**
     * Customer rejects the quotation via the shareable link.
     */
    public function reject(string $token, Request $request)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ]);

        $quotation = Quotation::where('approval_token', $token)->firstOrFail();

        if (!in_array($quotation->status, ['sent', 'draft'])) {
            return back()->with('error', 'This quotation can no longer be rejected.');
        }

        $quotation->update([
            'status'           => 'rejected',
            'rejected_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->route('quotations.public', $token)
            ->with('success', 'Your feedback has been recorded. We will reach out with a revised quotation.');
    }
}
