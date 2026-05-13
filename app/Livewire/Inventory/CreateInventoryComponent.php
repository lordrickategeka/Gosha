<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateInventoryComponent extends Component
{
    use WithFileUploads;

    // Basic fields
    public string $name = '';
    public string $sku = '';
    public string $barcode = '';
    public string $description = '';
    public string $notes = '';
    public string $item_type = 'service_part'; // service_part or wash_supply

    // Categorization
    public string $category_id = '';
    public string $supplier_id = '';
    public string $brand = '';

    // Service parts specific
    public string $oem_number = '';
    public string $aftermarket_number = '';
    public string $position = '';
    public string $condition = 'new';

    // Wash supplies specific
    public string $unit_of_measure = 'pcs';
    public string $expiry_date = '';
    public string $usage_rate = '';

    // Stock & pricing
    public string $unit = 'pcs';
    public float $quantity = 0;
    public float $cost_price = 0;
    public float $selling_price = 0;
    public float $reorder_level = 5;

    // Location
    public string $location = '';
    public $image;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'item_type' => 'required|in:service_part,wash_supply',
            'sku' => 'nullable|string|max:50|unique:inventory_items,sku',
            'barcode' => 'nullable|string|max:100',
            'category_id' => 'required|exists:inventory_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'brand' => 'nullable|string|max:100',

            // Service parts
            'oem_number' => 'nullable|string|max:100',
            'aftermarket_number' => 'nullable|string|max:100',
            'position' => 'nullable|string|in:front,rear,left,right,front_left,front_right,rear_left,rear_right,upper,lower,inner,outer,center,universal',
            'condition' => 'required_if:item_type,service_part|in:new,used,refurbished,reconditioned',

            // Wash supplies
            'unit_of_measure' => 'required|string|in:pcs,liters,ml,kg,g,meters,cm',
            'expiry_date' => 'nullable|date|after:today',
            'usage_rate' => 'nullable|numeric|min:0',

            // Common
            'unit' => 'required|string|max:20',
            'quantity' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:2048',
        ];
    }

    public function updatedItemType()
    {
        // Reset category when item type changes
        $this->category_id = '';

        // Set default unit_of_measure based on type
        if ($this->item_type === 'wash_supply') {
            $this->unit_of_measure = 'liters';
            $this->condition = 'new'; // Wash supplies are always new
        } else {
            $this->unit_of_measure = 'pcs';
        }
    }

    public function getCategoriesProperty()
    {
        $type = $this->item_type === 'service_part' ? 'service_parts' : 'wash_supplies';

        return InventoryCategory::where('type', $type)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getSuppliersProperty()
    {
        return Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function save()
    {
        $this->validate();

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('inventory-images', 'public');
        }

        $item = InventoryItem::create([
            'vendor_id' => auth()->user()->vendor_id,
            'branch_id' => session('current_branch_id') ?: null,
            'category_id' => $this->category_id,
            'supplier_id' => $this->supplier_id ?: null,
            'item_type' => $this->item_type,
            'name' => $this->name,
            'brand' => $this->brand ?: null,
            'sku' => $this->sku ?: null,
            'barcode' => $this->barcode ?: null,

            // Service parts specific
            'oem_number' => $this->item_type === 'service_part' ? ($this->oem_number ?: null) : null,
            'aftermarket_number' => $this->item_type === 'service_part' ? ($this->aftermarket_number ?: null) : null,
            'position' => $this->item_type === 'service_part' ? ($this->position ?: null) : null,
            'condition' => $this->condition,

            // Wash supplies specific
            'unit_of_measure' => $this->unit_of_measure,
            'expiry_date' => $this->item_type === 'wash_supply' && $this->expiry_date
                ? $this->expiry_date
                : null,
            'usage_rate' => $this->item_type === 'wash_supply' && $this->usage_rate
                ? $this->usage_rate
                : null,

            // Common fields
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'reorder_level' => $this->reorder_level,
            'location' => $this->location ?: null,
            'description' => $this->description ?: null,
            'notes' => $this->notes ?: null,
            'image_path' => $imagePath,
            'is_active' => true,
        ]);

        // Create initial stock movement if quantity > 0
        if ($this->quantity > 0) {
            $item->movements()->create([
                'branch_id' => session('current_branch_id'),
                'movement_type' => 'purchase',
                'quantity_change' => $this->quantity,
                'quantity_after' => $this->quantity,
                'unit_cost' => $this->cost_price,
                'total_cost' => $this->quantity * $this->cost_price,
                'supplier_id' => $this->supplier_id ?: null,
                'performed_by' => auth()->id(),
                'notes' => 'Initial stock',
                'movement_date' => now(),
            ]);
        }

        session()->flash('success', 'Inventory item created successfully.');

        return redirect()->route('inventory.show', $item);
    }

    public function render()
    {
        return view('livewire.inventory.create-inventory-component')
            ->layout('components.layouts.app', ['title' => 'Add Inventory Item']);
    }
}
