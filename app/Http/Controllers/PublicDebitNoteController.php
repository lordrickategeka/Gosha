<?php

namespace App\Http\Controllers;

use App\Models\DebitNote;
use App\Models\InventoryMovement;
use App\Models\InvoiceItem;
use App\Models\WorkOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicDebitNoteController extends Controller
{
    public function show(string $token)
    {
        $debitNote = DebitNote::with([
            'items.inventoryItem',
            'workOrder .vehicle',
            'workOrder.customer',
            'customer',
            'invoice.items',
        ])->where('approval_token', $token)->firstOrFail();

        return view('public.debit-note', compact('debitNote'));
    }

    public function submit(string $token, Request $request)
    {
$debitNote = DebitNote::with(['items', 'workOrder', 'invoice.items', 'quotation.items'])->where('approval_token', $token)->firstOrFail();

        if (!$debitNote->canBeRespondedTo()) {
            return back()->with('error', 'This debit note can no longer be responded to.');
        }

        $validated = $request->validate([
            'approve_all' => 'nullable|boolean',
            'items' => 'required|array',
            'items.*.decision' => 'required|in:approved,rejected',
            'items.*.rejection_reason' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($debitNote, $validated) {
            $approveAll = (bool) ($validated['approve_all'] ?? false);

            foreach ($debitNote->items as $item) {
                $lineInput = $validated['items'][$item->id] ?? null;

                if ($approveAll) {
                    $decision = 'approved';
                    $reason = null;
                } else {
                    if (!$lineInput) {
                        continue;
                    }

                    $decision = $lineInput['decision'];
                    $reason = $decision === 'rejected' ? ($lineInput['rejection_reason'] ?? null) : null;
                }

                $item->update([
                    'customer_decision' => $decision,
                    'rejection_reason' => $reason,
                ]);
            }

            $debitNote->refresh();
            $items = $debitNote->items;

            $approvedCount = $items->where('customer_decision', 'approved')->count();
            $rejectedCount = $items->where('customer_decision', 'rejected')->count();

            if ($approvedCount > 0 && $rejectedCount === 0) {
                $debitNote->status = 'approved';
            } elseif ($approvedCount > 0 && $rejectedCount > 0) {
                $debitNote->status = 'partially_approved';
            } else {
                $debitNote->status = 'rejected';
            }

            $debitNote->responded_at = now();
            $debitNote->save();

            if ($approvedCount > 0) {
                $this->applyApprovedItems($debitNote);
                $debitNote->update(['status' => 'applied']);
            }

            if ($rejectedCount > 0) {
                $this->recordConsumerReturns($debitNote);
            }
        });

        return redirect()->route('debit-notes.public', $token)
            ->with('success', 'Your debit note response has been submitted successfully.');
    }

protected function applyApprovedItems(DebitNote $debitNote): void
    {
        $approvedItems = $debitNote->items()->where('customer_decision', 'approved')->get();
        $workOrder = $debitNote->workOrder;
        $invoice = $debitNote->invoice;
        $quotation = $debitNote->quotation;

        foreach ($approvedItems as $item) {
            $workOrderItem = null;

            if ($item->work_order_item_id) {
                $workOrderItem = WorkOrderItem::where('work_order_id', $workOrder->id)
                    ->where('id', $item->work_order_item_id)
                    ->first();
            }

            if ($workOrderItem) {
                $workOrderItem->update([
                    'item_type' => $item->item_type,
                    'description' => $item->description,
                    'inventory_item_id' => $item->inventory_item_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'total' => $item->total,
                ]);
            } else {
                $workOrderItem = WorkOrderItem::create([
                    'work_order_id' => $workOrder->id,
                    'item_type' => $item->item_type,
                    'description' => $item->description,
                    'inventory_item_id' => $item->inventory_item_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'total' => $item->total,
                ]);
            }

            // Sync to Invoice
            if ($invoice) {
                $invoiceItem = $invoice->items()
                    ->where('description', $item->description)
                    ->first();

                if ($invoiceItem) {
                    $invoiceItem->update([
                        'item_type' => $item->item_type,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount,
                        'total' => $item->total,
                    ]);
                } else {
                    $invoice->items()->create([
                        'item_type' => $item->item_type,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount,
                        'tax' => 0,
                        'total' => $item->total,
                    ]);
                }
            }

            // Sync to Quotation
            if ($quotation) {
                $quotationItem = $quotation->items()
                    ->where('description', $item->description)
                    ->first();

                if ($quotationItem) {
                    $quotationItem->update([
                        'item_type' => $item->item_type,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount,
                        'total' => $item->total,
]);
                } else {
                    $quotation->items()->create([
                        'quotation_id' => $quotation->id,
                        'inventory_item_id' => $item->inventory_item_id,
                        'item_type' => $item->item_type,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount,
                        'vat_rate' => $quotation->vat_rate ?? 0,
                        'vat_applicable' => true,
                        'total' => $item->total,
                    ]);
                }

                $quotation->refresh();
                $quotation->recalculateTotals();
                $quotation->save();
            }
        }

        if ($invoice) {
            $invoice->save();
        }
    }

    protected function recordConsumerReturns(DebitNote $debitNote): void
    {
        $rejectedPartItems = $debitNote->items()
            ->where('customer_decision', 'rejected')
            ->where('item_type', 'part')
            ->whereNotNull('inventory_item_id')
            ->get();

        foreach ($rejectedPartItems as $item) {
            if (!$item->inventoryItem) {
                continue;
            }

            $quantityAfter = $item->inventoryItem->quantity + $item->quantity;

            InventoryMovement::create([
                'inventory_item_id' => $item->inventory_item_id,
                'branch_id' => $debitNote->branch_id,
                'movement_type' => 'customer_return',
                'quantity_change' => $item->quantity,
                'quantity_after' => $quantityAfter,
                'unit_cost' => $item->inventoryItem->cost_price,
                'total_cost' => $item->quantity * $item->inventoryItem->cost_price,
                'reason' => 'Rejected debit note item returned by customer',
                'movable_type' => DebitNote::class,
                'movable_id' => $debitNote->id,
                'performed_by' => null,
            ]);

            $item->inventoryItem->increment('quantity', $item->quantity);
        }
    }
}
