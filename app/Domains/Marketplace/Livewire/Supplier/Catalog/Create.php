<?php

namespace App\Domains\Marketplace\Livewire\Supplier\Catalog;

use App\Domains\Marketplace\Models\CatalogProduct;
use App\Domains\Marketplace\Models\PartCategory;
use App\Domains\Platform\Models\Vendor;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Propose a new catalog product. Gated to verified suppliers only — an unverified supplier
 * account must not be able to go live with new catalog entries, even ones still pending
 * platform moderation (is_verified stays false here regardless; a platform admin flips it
 * in the Catalog moderation area).
 */
class Create extends Component
{
    public string $brand = '';

    public string $part_number = '';

    public string $name = '';

    public ?int $category_id = null;

    public string $unit_of_measure = 'unit';

    public string $description = '';

    private function vendor(): ?Vendor
    {
        $vendorId = session('current_vendor_id') ?? auth()->user()?->vendor_id;

        return $vendorId ? Vendor::find($vendorId) : null;
    }

    public function mount(): void
    {
        $vendor = $this->vendor();

        abort_unless($vendor?->isVerifiedSupplier(), 403, 'Your supplier account must be verified before you can add catalog products.');
        abort_unless(config('marketplace.allow_supplier_catalog_submissions', true), 403, 'Supplier catalog submissions are currently disabled.');
    }

    protected function rules(): array
    {
        return [
            'brand' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:part_categories,id',
            'unit_of_measure' => 'required|in:unit,litre,set,pair,metre',
            'description' => 'nullable|string|max:2000',
        ];
    }

    public function save()
    {
        $this->authorize('manage_catalog');

        $vendor = $this->vendor();
        abort_unless($vendor?->isVerifiedSupplier(), 403, 'Your supplier account must be verified before you can add catalog products.');
        abort_unless(config('marketplace.allow_supplier_catalog_submissions', true), 403, 'Supplier catalog submissions are currently disabled.');

        $this->validate();

        if ($this->brand && $this->part_number) {
            $exists = CatalogProduct::where('brand', $this->brand)->where('part_number', $this->part_number)->exists();
            if ($exists) {
                $this->addError('part_number', 'A catalog product with this brand and part number already exists.');
                return;
            }
        }

        $slug = Str::slug($this->brand . ' ' . $this->name . ' ' . $this->part_number);
        if (CatalogProduct::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(6);
        }

        $product = CatalogProduct::create([
            'brand' => $this->brand ?: null,
            'part_number' => $this->part_number ?: null,
            'name' => $this->name,
            'slug' => $slug,
            'category_id' => $this->category_id,
            'unit_of_measure' => $this->unit_of_measure,
            'description' => $this->description ?: null,
            'created_by_vendor_id' => $vendor->id,
            'is_verified' => false,
            'is_active' => true,
        ]);

        $this->dispatch('toast', message: "\"{$product->name}\" submitted for platform review.");

        return $this->redirectRoute('supplier.catalog.index', navigate: true);
    }

    #[Computed]
    public function categories()
    {
        return PartCategory::where('is_active', true)->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.marketplace.supplier.catalog.create');
    }
}
