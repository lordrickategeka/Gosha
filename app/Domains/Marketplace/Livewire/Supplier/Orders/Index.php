<?php

namespace App\Domains\Marketplace\Livewire\Supplier\Orders;

use App\Domains\Marketplace\Enums\PurchaseOrderStatus;
use App\Domains\Marketplace\Models\PurchaseOrder;
use App\Domains\Marketplace\Services\PurchaseOrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public ?int $markingPaidId = null;

    public $paymentAmount = null;

    private function getVendorId(): ?int
    {
        $vendorId = session('current_vendor_id') ?? auth()->user()?->vendor_id;

        return $vendorId ? (int) $vendorId : null;
    }

    private function hasVendor(): bool
    {
        // Platform users (super admins) can view without a vendor
        if (auth()->user()?->is_platform_user) {
            return true;
        }

        return $this->getVendorId() !== null;
    }

    public function mount()
    {
        // Redirect to vendor registration if no vendor found and not a platform user
        if (!$this->hasVendor()) {
            return $this->redirect(route('register'), navigate: true);
        }
    }

    public function accept(int $id): void
    {
        $this->authorize('manage_purchase_orders');
        $po = PurchaseOrder::asSupplier($this->getVendorId())->findOrFail($id);
        // Transition into Accepted triggers commission metering via PurchaseOrderObserver.
        $po->update(['status' => PurchaseOrderStatus::Accepted]);
        $this->dispatch('toast', message: "PO {$po->po_number} accepted.");
    }

    public function downloadInvoicePdf(int $id)
    {
        $this->authorize('view_purchase_orders');

        $po = PurchaseOrder::asSupplier($this->getVendorId())
            ->with(['items.product', 'buyer', 'supplier'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.marketplace-invoice', ['po' => $po]);

        return response()->streamDownload(fn () => print($pdf->output()), "Invoice-{$po->po_number}.pdf");
    }

    public function openMarkPaid(int $id): void
    {
        $po = PurchaseOrder::asSupplier($this->getVendorId())->findOrFail($id);
        $this->markingPaidId = $po->id;
        $this->paymentAmount = round((float) $po->total - (float) $po->amount_paid, 2);
    }

    public function markPaid(PurchaseOrderService $poService): void
    {
        $this->authorize('manage_purchase_orders');

        $po = PurchaseOrder::asSupplier($this->getVendorId())->findOrFail($this->markingPaidId);

        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
        ]);

        $poService->markPaid($po, (float) $this->paymentAmount);

        $this->markingPaidId = null;
        $this->paymentAmount = null;
        $this->dispatch('toast', message: "Payment recorded for PO {$po->po_number}.");
    }

    public function render()
    {
        $orders = PurchaseOrder::asSupplier($this->getVendorId())
            ->with(['buyer', 'items'])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.marketplace.supplier.orders.index', [
            'orders' => $orders,
            'statuses' => PurchaseOrderStatus::cases(),
        ]);
    }
}
