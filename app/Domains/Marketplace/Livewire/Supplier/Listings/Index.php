<?php

namespace App\Domains\Marketplace\Livewire\Supplier\Listings;

use App\Domains\Marketplace\Models\CatalogProduct;
use App\Domains\Marketplace\Models\MarketplaceListing;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    // Inline create/edit form state.
    public bool $showForm = false;
    public ?int $editingId = null;
    public ?int $catalog_product_id = null;
    public string $supplier_sku = '';
    public $price = null;
    public int $stock_qty = 0;
    public int $min_order_qty = 1;
    public int $lead_time_days = 0;
    public string $condition = 'new';
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'catalog_product_id' => 'required|exists:catalog_products,id',
            'supplier_sku' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'min_order_qty' => 'required|integer|min:1',
            'lead_time_days' => 'required|integer|min:0',
            'condition' => 'required|in:new,used,refurbished',
            'is_active' => 'boolean',
        ];
    }

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

    public function openCreate(): void
    {
        $this->reset(['editingId', 'catalog_product_id', 'supplier_sku', 'price', 'stock_qty', 'min_order_qty', 'lead_time_days', 'condition', 'is_active']);
        $this->is_active = true;
        $this->min_order_qty = 1;
        $this->condition = 'new';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $listing = MarketplaceListing::ownedBySupplier($this->getVendorId())->findOrFail($id);
        $this->editingId = $listing->id;
        $this->catalog_product_id = $listing->catalog_product_id;
        $this->supplier_sku = (string) $listing->supplier_sku;
        $this->price = $listing->price;
        $this->stock_qty = $listing->stock_qty;
        $this->min_order_qty = $listing->min_order_qty;
        $this->lead_time_days = $listing->lead_time_days;
        $this->condition = $listing->condition;
        $this->is_active = $listing->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize('manage_listings');
        $data = $this->validate();
        $data['supplier_vendor_id'] = $this->getVendorId();
        $data['currency'] = config('marketplace.default_currency', 'UGX');

        if ($this->editingId) {
            MarketplaceListing::ownedBySupplier($this->getVendorId())
                ->findOrFail($this->editingId)
                ->update($data);
        } else {
            MarketplaceListing::create($data);
        }

        $this->showForm = false;
        $this->dispatch('toast', message: 'Listing saved.');
    }

    public function toggle(int $id): void
    {
        $this->authorize('manage_listings');
        $listing = MarketplaceListing::ownedBySupplier($this->getVendorId())->findOrFail($id);
        $listing->update(['is_active' => ! $listing->is_active]);
    }

    #[Computed]
    public function products()
    {
        return CatalogProduct::active()->orderBy('name')->limit(200)->get();
    }

    public function render()
    {
        $vendorId = $this->getVendorId();

        $listings = MarketplaceListing::ownedBySupplier($vendorId)
            ->with('product')
            ->when($this->search, fn ($q) => $q->whereHas('product', fn ($p) =>
                $p->where('name', 'like', "%{$this->search}%")
                  ->orWhere('part_number', 'like', "%{$this->search}%")))
            ->latest()
            ->paginate(15);

        return view('livewire.marketplace.supplier.listings.index', compact('listings'));
    }
}
