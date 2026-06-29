<?php

namespace App\Domains\Marketplace\Livewire\Buyer\Rfq;

use App\Domains\Marketplace\Enums\RfqStatus;
use App\Domains\Marketplace\Models\CatalogProduct;
use App\Domains\Marketplace\Models\Rfq;
use App\Domains\Marketplace\Services\RfqService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    public string $title = '';
    public string $notes = '';
    public string $visibility = 'open';
    public ?string $closes_at = null;

    /** Each row: ['catalog_product_id' => ?int, 'description' => string, 'qty' => int, 'target_price' => ?float] */
    public array $items = [];

    /** Targeted RFQ invitees (supplier vendor ids). */
    public array $invitees = [];

    public function mount(): void
    {
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = ['catalog_product_id' => null, 'description' => '', 'qty' => 1, 'target_price' => null];
    }

    public function removeItem(int $i): void
    {
        unset($this->items[$i]);
        $this->items = array_values($this->items);
    }

    protected function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'visibility' => 'required|in:open,targeted',
            'closes_at' => 'nullable|date|after:now',
            'items' => 'required|array|min:1',
            'items.*.catalog_product_id' => 'nullable|exists:catalog_products,id',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.target_price' => 'nullable|numeric|min:0',
            'invitees' => 'array',
        ];
    }

    public function save(RfqService $rfqService)
    {
        $this->authorize('manage_rfqs');
        $this->validate();

        foreach ($this->items as $i => $row) {
            if (! $row['catalog_product_id'] && blank($row['description'])) {
                $this->addError("items.$i.description", 'Pick a product or describe the item.');
                return;
            }
        }

        $vendorId = session('current_vendor_id') ?? auth()->user()->vendor_id;

        $rfq = Rfq::create([
            'reference' => $rfqService->nextReference(),
            'buyer_vendor_id' => $vendorId,
            'branch_id' => session('current_branch_id'),
            'created_by' => auth()->id(),
            'title' => $this->title ?: null,
            'notes' => $this->notes ?: null,
            'visibility' => $this->visibility,
            'status' => RfqStatus::Draft,
            'closes_at' => $this->closes_at,
        ]);

        foreach ($this->items as $row) {
            $rfq->items()->create([
                'catalog_product_id' => $row['catalog_product_id'] ?: null,
                'description' => $row['description'] ?: null,
                'qty' => (int) $row['qty'],
                'target_price' => $row['target_price'] ?: null,
            ]);
        }

        $rfqService->publish($rfq, $this->visibility === 'targeted' ? $this->invitees : []);

        $this->dispatch('toast', message: "RFQ {$rfq->reference} published.");
        return $this->redirectRoute('marketplace.rfqs.index', navigate: true);
    }

    #[Computed]
    public function products()
    {
        return CatalogProduct::active()->orderBy('name')->limit(300)->get();
    }

    public function render()
    {
        return view('livewire.marketplace.buyer.rfq.create');
    }
}
