<?php

namespace App\Domains\Marketplace\Livewire\Supplier\Quotes;

use App\Domains\Marketplace\Enums\QuoteStatus;
use App\Domains\Marketplace\Enums\RfqStatus;
use App\Domains\Marketplace\Models\Quote;
use App\Domains\Marketplace\Models\Rfq;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Supplier quote builder for one RFQ.
 *
 * One line per RFQ item (qty defaults to the requested qty but is editable — a supplier may
 * only be able to fill part of the request). Saves as a draft or submits; a submitted quote
 * becomes read-only here (it's edited by withdrawing on the buyer side, not by resubmitting).
 * The DB unique(['rfq_id','supplier_vendor_id']) means at most one quote per supplier per RFQ,
 * so this component upserts against that row rather than always creating a new one.
 */
class Compose extends Component
{
    public Rfq $rfq;

    public ?int $quoteId = null;

    public string $valid_until = '';

    public string $notes = '';

    /** Keyed by rfq_item_id: ['qty' => int, 'unit_price' => ?float, 'tax_rate' => float] */
    public array $lines = [];

    public bool $readonly = false;

    private function vendorId(): ?int
    {
        $vendorId = session('current_vendor_id') ?? auth()->user()?->vendor_id;

        return $vendorId ? (int) $vendorId : null;
    }

    public function mount(Rfq $rfq): void
    {
        $this->authorize('manage_quotes');

        $vendorId = $this->vendorId();
        abort_if($vendorId === null, 403);
        app(\App\Domains\Marketplace\Services\SupplierPlanAccess::class)->assertSelling(\App\Domains\Platform\Models\Vendor::find($vendorId));

        $this->rfq = $rfq->load(['items.product', 'buyer']);

        $invited = $this->rfq->invitations()->where('supplier_vendor_id', $vendorId)->exists();
        abort_unless($this->rfq->isOpen() || $invited, 403, 'You are not invited to quote on this RFQ.');

        $existing = Quote::asSupplier($vendorId)
            ->where('rfq_id', $this->rfq->id)
            ->with('items')
            ->first();

        $this->quoteId = $existing?->id;
        $this->readonly = ($existing && $existing->status !== QuoteStatus::Draft)
            || $this->rfq->status !== RfqStatus::Published;

        $this->notes = $existing->notes ?? '';
        $this->valid_until = optional($existing?->valid_until)->format('Y-m-d') ?? '';

        foreach ($this->rfq->items as $item) {
            $line = $existing?->items->firstWhere('rfq_item_id', $item->id);
            $this->lines[$item->id] = [
                'qty' => $line->qty ?? $item->qty,
                'unit_price' => $line?->unit_price !== null ? (float) $line->unit_price : null,
                'tax_rate' => $line?->tax_rate !== null ? (float) $line->tax_rate : 0,
            ];
        }
    }

    protected function rules(): array
    {
        return [
            'valid_until' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string|max:2000',
            'lines.*.qty' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ];
    }

    #[Computed]
    public function total(): float
    {
        return round(collect($this->lines)->sum(function (array $row) {
            $qty = (int) ($row['qty'] ?? 0);
            $price = (float) ($row['unit_price'] ?? 0);
            $tax = (float) ($row['tax_rate'] ?? 0);

            return $qty * $price * (1 + $tax / 100);
        }), 2);
    }

    public function saveDraft(): void
    {
        $quote = $this->persist(QuoteStatus::Draft);

        $this->quoteId = $quote->id;
        $this->dispatch('toast', message: 'Draft saved.');
    }

    public function submit(): void
    {
        $quote = $this->persist(QuoteStatus::Submitted);

        $this->dispatch('toast', message: "Quote {$quote->reference} submitted.");
        $this->redirectRoute('supplier.quotes.inbox', navigate: true);
    }

    /**
     * Re-derives every guard from the database rather than trusting $this->readonly / $this->rfq,
     * since plain Livewire properties round-trip through the client and can't be trusted as-is.
     */
    private function persist(QuoteStatus $status): Quote
    {
        $this->authorize('manage_quotes');
        $this->validate();

        $vendorId = $this->vendorId();
        abort_if($vendorId === null, 403);

        $rfq = Rfq::findOrFail($this->rfq->id);
        app(\App\Domains\Marketplace\Services\SupplierPlanAccess::class)->assertSelling(\App\Domains\Platform\Models\Vendor::find($vendorId));
        abort_unless($rfq->status === RfqStatus::Published, 422, 'This RFQ is no longer accepting quotes.');

        $quote = Quote::asSupplier($vendorId)->where('rfq_id', $rfq->id)->first();
        abort_if($quote && $quote->status !== QuoteStatus::Draft, 422, 'This quote has already been submitted and can no longer be edited.');

        if (! $quote) {
            $quote = Quote::create([
                'reference' => 'QT-'.now()->format('Y').'-'.Str::padLeft((string) (Quote::max('id') + 1), 6, '0'),
                'rfq_id' => $rfq->id,
                'supplier_vendor_id' => $vendorId,
                'currency' => config('marketplace.default_currency'),
                'status' => $status,
            ]);
        }

        $quote->update([
            'notes' => $this->notes ?: null,
            'valid_until' => $this->valid_until ?: null,
            'status' => $status,
        ]);

        foreach ($this->rfq->items as $item) {
            $row = $this->lines[$item->id];

            $quote->items()->updateOrCreate(
                ['rfq_item_id' => $item->id],
                [
                    'catalog_product_id' => $item->catalog_product_id,
                    'description' => $item->description,
                    'qty' => (int) $row['qty'],
                    'unit_price' => (float) $row['unit_price'],
                    'tax_rate' => (float) ($row['tax_rate'] ?: 0),
                ]
            );
        }

        $quote->recalculateTotals();

        return $quote;
    }

    public function render()
    {
        return view('livewire.marketplace.supplier.quotes.compose');
    }
}
