<?php

namespace App\Domains\Finance\Livewire\Quotations;

use App\Domains\Finance\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class ShowQuotationComponent extends Component
{
    public Quotation $quotation;
    public bool $showRejectModal = false;
    public string $rejectionReason = '';

    public function mount(Quotation $quotation): void
    {
        $this->quotation = $quotation->load([
            'items.inventoryItem',
            'items.supplier',
            'workOrder.vehicle',
            'workOrder.customer',
            'customer',
            'createdBy',
            'approvedByUser',
            'parentQuotation',
            'revisions',
        ]);
    }

    // ─── Actions ─────────────────────────────────────────────────────────────

    public function sendToCustomer(): void
    {
        $this->authorize('send_quotations');

        if (!$this->quotation->isDraft()) {
            session()->flash('error', 'Only draft quotations can be sent.');
            return;
        }

        $this->quotation->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);
        $this->quotation->refresh();

        // Also update work order status
        $this->quotation->workOrder->update(['status' => 'quoted']);

        session()->flash('success', 'Quotation marked as sent. Share the link with the customer.');
    }

    public function approveOnBehalf(): void
    {
        $this->authorize('approve_quotations');

        if (!$this->quotation->canBeApproved()) {
            session()->flash('error', 'This quotation cannot be approved (check status or validity date).');
            return;
        }

        $this->quotation->update([
            'status'             => 'approved',
            'approved_at'        => now(),
            'approved_by_user_id'=> auth()->id(),
        ]);

        $this->syncApprovalToWorkOrder();
        $this->quotation->refresh();

        session()->flash('success', 'Quotation approved.');
    }

    public function openRejectModal(): void
    {
        $this->showRejectModal = true;
        $this->rejectionReason = '';
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectionReason = '';
    }

    public function rejectOnBehalf(): void
    {
        $this->authorize('approve_quotations');

        $this->validate([
            'rejectionReason' => 'required|string|min:5|max:1000',
        ]);

        $this->quotation->update([
            'status'           => 'rejected',
            'rejected_at'      => now(),
            'rejection_reason' => $this->rejectionReason,
        ]);
        $this->quotation->refresh();

        // Release any reservations held for the linked work order
        if ($this->quotation->workOrder) {
            $inventoryService = new \App\Domains\Inventory\Services\InventoryService();
            $inventoryService->releaseReservationsForWorkOrder($this->quotation->workOrder);
        }

        $this->showRejectModal = false;

        session()->flash('success', 'Quotation rejected.');
    }

    public function downloadPdf()
    {
        $this->authorize('view_quotations');

        $pdf = Pdf::loadView('pdf.quotation', ['quotation' => $this->quotation]);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'Quotation-' . $this->quotation->quotation_number . '.pdf'
        );
    }

    // ─── Internal helpers ────────────────────────────────────────────────────

    protected function syncApprovalToWorkOrder(): void
    {
        $workOrder = $this->quotation->workOrder;
        foreach ($this->quotation->items as $item) {
            $woItem = $workOrder->items
                ->where('description', $item->description)
                ->first();

            if ($woItem) {
                $woItem->update([
                    'unit_price'  => $item->unit_price,
                    'discount'    => $item->discount,
                    'total'       => $item->total,
                    'supplier_id' => $item->supplier_id,
                ]);
            }
        }
    }

    // ─── Computed ────────────────────────────────────────────────────────────

    public function getWhatsappUrlProperty(): string
    {
        return $this->quotation->whatsappShareUrl();
    }

    public function getApprovalUrlProperty(): string
    {
        return $this->quotation->approvalUrl();
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.quotations.show-quotation')
            ->layout('components.layouts.app', [
                'title' => 'Quotation ' . $this->quotation->quotation_number,
            ]);
    }
}
