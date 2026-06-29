<?php

namespace App\Domains\Inventory\Livewire\Inventory;

use App\Domains\Inventory\Models\InventoryItem;
use App\Domains\Inventory\Models\InventoryMovement;
use App\Domains\Inventory\Services\InventoryService;
use Livewire\Component;

class ShowInventoryComponent extends Component
{
    public InventoryItem $item;

    // Modals
    public bool $showAdjustModal = false;
    public bool $showTransferModal = false;
    public bool $showPurchaseModal = false;

    // Adjust stock fields
    public string $adjustType = 'adjustment_in';
    public float $adjustQuantity = 0;
    public string $adjustNotes = '';

    // Transfer fields
    public string $transferToBranchId = '';
    public float $transferQuantity = 0;
    public string $transferNotes = '';

    // Purchase fields
    public float $purchaseQuantity = 0;
    public float $purchaseUnitCost = 0;
    public string $purchaseSupplierId = '';
    public string $purchaseNotes = '';

    protected InventoryService $inventoryService;

    public function boot(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function mount(InventoryItem $inventoryItem)
    {
        $this->item = $inventoryItem->load([
            'category',
            'supplier',
            'branch',
            'movements' => fn($q) => $q->with(['performedBy', 'supplier', 'fromBranch', 'toBranch'])
                ->latest()
                ->take(20)
        ]);

        // Set default purchase unit cost to current cost price
        $this->purchaseUnitCost = $this->item->cost_price;
    }

    public function openAdjustModal()
    {
        $this->showAdjustModal = true;
        $this->adjustQuantity = 0;
        $this->adjustNotes = '';
    }

    public function openTransferModal()
    {
        $this->showTransferModal = true;
        $this->transferQuantity = 0;
        $this->transferToBranchId = '';
        $this->transferNotes = '';
    }

    public function openPurchaseModal()
    {
        $this->showPurchaseModal = true;
        $this->purchaseQuantity = 0;
        $this->purchaseUnitCost = $this->item->cost_price;
        $this->purchaseSupplierId = $this->item->supplier_id ?? '';
        $this->purchaseNotes = '';
    }

    public function adjustStock()
    {
        $this->validate([
            'adjustType' => 'required|in:adjustment_in,adjustment_out,wastage,return_to_supplier',
            'adjustQuantity' => 'required|numeric|min:0.01',
            'adjustNotes' => 'required|string|max:500',
        ]);

        $quantityChange = in_array($this->adjustType, ['adjustment_out', 'wastage', 'return_to_supplier'])
            ? -$this->adjustQuantity
            : $this->adjustQuantity;

        // Check if sufficient stock for negative adjustments
        if ($quantityChange < 0 && $this->item->quantity < abs($quantityChange)) {
            $this->addError('adjustQuantity', 'Insufficient stock for this adjustment.');
            return;
        }

        try {
            InventoryMovement::create([
                'inventory_item_id' => $this->item->id,
                'branch_id' => $this->item->branch_id,
                'movement_type' => $this->adjustType,
                'quantity_change' => $quantityChange,
                'quantity_after' => $this->item->quantity + $quantityChange,
                'unit_cost' => $this->item->cost_price,
                'total_cost' => abs($quantityChange) * $this->item->cost_price,
                'performed_by' => auth()->id(),
                'notes' => $this->adjustNotes,
                'movement_date' => now(),
            ]);

            $this->item->increment('quantity', $quantityChange);
            $this->item->refresh();

            $this->showAdjustModal = false;
            $this->reset(['adjustQuantity', 'adjustNotes']);

            session()->flash('success', 'Stock adjusted successfully.');
        } catch (\Exception $e) {
            $this->addError('adjustQuantity', 'Failed to adjust stock: ' . $e->getMessage());
        }
    }

    public function transferStock()
    {
        $this->validate([
            'transferToBranchId' => 'required|exists:branches,id|different:' . $this->item->branch_id,
            'transferQuantity' => 'required|numeric|min:0.01|max:' . $this->item->quantity,
            'transferNotes' => 'nullable|string|max:500',
        ]);

        try {
            $this->inventoryService->transferStock(
                inventoryItem: $this->item,
                quantity: $this->transferQuantity,
                fromBranchId: $this->item->branch_id,
                toBranchId: (int) $this->transferToBranchId,
                notes: $this->transferNotes ?: null
            );

            $this->item->refresh();
            $this->showTransferModal = false;
            $this->reset(['transferQuantity', 'transferToBranchId', 'transferNotes']);

            session()->flash('success', 'Stock transferred successfully.');
        } catch (\Exception $e) {
            $this->addError('transferQuantity', 'Transfer failed: ' . $e->getMessage());
        }
    }

    public function purchaseStock()
    {
        $this->validate([
            'purchaseQuantity' => 'required|numeric|min:0.01',
            'purchaseUnitCost' => 'required|numeric|min:0',
            'purchaseSupplierId' => 'nullable|exists:suppliers,id',
            'purchaseNotes' => 'nullable|string|max:500',
        ]);

        try {
            $this->inventoryService->addStock(
                inventoryItem: $this->item,
                quantity: $this->purchaseQuantity,
                supplierId: $this->purchaseSupplierId ? (int) $this->purchaseSupplierId : null,
                unitCost: $this->purchaseUnitCost,
                notes: $this->purchaseNotes ?: null
            );

            $this->item->refresh();
            $this->showPurchaseModal = false;
            $this->reset(['purchaseQuantity', 'purchaseUnitCost', 'purchaseSupplierId', 'purchaseNotes']);

            session()->flash('success', 'Stock purchase recorded successfully.');
        } catch (\Exception $e) {
            $this->addError('purchaseQuantity', 'Purchase failed: ' . $e->getMessage());
        }
    }

    public function getAvailableBranchesProperty()
    {
        return auth()->user()->vendor->branches()
            ->where('id', '!=', $this->item->branch_id)
            ->orderBy('name')
            ->get();
    }

    public function getSuppliersProperty()
    {
        return auth()->user()->vendor->suppliers()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.inventory.show-inventory-component')
            ->layout('components.layouts.app', ['title' => $this->item->name]);
    }
}
