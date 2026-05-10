<?php

namespace App\Livewire\Quotations;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Supplier;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class QuotationBuilderComponent extends Component
{
    // ─── Work Order & Quotation context ─────────────────────────────────────
    public WorkOrder $workOrder;
    public ?Quotation $quotation = null;

    // ─── Line items ─────────────────────────────────────────────────────────
    public array $items = [];

    // Live inventory search suggestions per row [index => ['my_branch' => [...], 'other_branches' => [...]]]
    public array $itemSuggestions = [];

    // ─── Quotation header fields ────────────────────────────────────────────
    public string $notes = '';
    public string $termsAndConditions = '';
    public string $validUntil = '';
    public float $vatRate = 0;

    // ─── Add-to-inventory modal ─────────────────────────────────────────────
    public bool $showAddToInventoryModal = false;
    public ?int $addToInventoryIndex = null;
    public string $newItemName = '';
    public string $newItemSku = '';
    public ?int $newItemCategoryId = null;
    public ?int $newItemSupplierId = null;
    public string $newItemUnit = 'pcs';
    public float $newItemCostPrice = 0;
    public float $newItemSellingPrice = 0;

    // ─── Cached lookups ─────────────────────────────────────────────────────
    public array $suppliers = [];

    // ═══════════════════════════════════════════════════════════════════════
    // LIFECYCLE
    // ═══════════════════════════════════════════════════════════════════════

    public function mount(WorkOrder $workOrder, ?Quotation $quotation = null): void
    {
        $this->workOrder = $workOrder->load(['items', 'vehicle', 'customer', 'branch']);
        $this->quotation = $quotation?->load('items.inventoryItem', 'items.supplier');

        $this->loadSuppliers();
        $this->validUntil = now()->addDays(14)->format('Y-m-d');

        if ($this->quotation) {
            // Editing existing quotation
            $this->notes = $this->quotation->notes ?? '';
            $this->termsAndConditions = $this->quotation->terms_and_conditions ?? '';
            $this->validUntil = $this->quotation->valid_until?->format('Y-m-d') ?? $this->validUntil;
            $this->vatRate = (float) ($this->quotation->vat_rate ?? 0);

            foreach ($this->quotation->items->sortBy('sort_order') as $item) {
                $this->items[] = [
                    'item_type'         => $item->item_type,
                    'description'       => $item->description,
                    'inventory_item_id' => $item->inventory_item_id,
                    'supplier_id'       => $item->supplier_id,
                    'quantity'          => (float) $item->quantity,
                    'unit_price'        => (float) $item->unit_price,
                    'discount'          => (float) $item->discount,
                    'vat_applicable'    => (bool) $item->vat_applicable,
                ];
            }
        } else {
            // Seed rows from work order items (description + type, zero prices)
            foreach ($this->workOrder->items as $woItem) {
                $this->items[] = [
                    'item_type'         => $woItem->item_type,
                    'description'       => $woItem->description,
                    'inventory_item_id' => $woItem->inventory_item_id,
                    'supplier_id'       => null,
                    'quantity'          => (float) $woItem->quantity,
                    'unit_price'        => max(0, (float) $woItem->unit_price),
                    'discount'          => 0.0,
                    'vat_applicable'    => false,
                ];
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ITEMS MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════════

    public function addItem(string $type = 'labor'): void
    {
        $this->items[] = [
            'item_type'         => $type,
            'description'       => '',
            'inventory_item_id' => null,
            'supplier_id'       => null,
            'quantity'          => 1,
            'unit_price'        => 0,
            'discount'          => 0,
            'vat_applicable'    => false,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        unset($this->itemSuggestions[$index]);
        $this->itemSuggestions = array_values($this->itemSuggestions);
    }

    // ─── Live inventory search ────────────────────────────────────────────

    public function updatedItems($value, string $key): void
    {
        if (!str_ends_with($key, '.description')) {
            return;
        }

        $index = (int) explode('.', $key)[0];
        $term  = (string) $value;

        if (strlen($term) < 2 || !empty($this->items[$index]['inventory_item_id'])) {
            $this->itemSuggestions[$index] = ['my_branch' => [], 'other_branches' => []];
            return;
        }

        $branchId = (int) $this->workOrder->branch_id;
        $vendorId = auth()->user()->vendor_id;

        $myBranch = InventoryItem::where('is_active', true)
            ->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%");
            })
            ->orderByDesc('quantity')
            ->limit(8)
            ->get()
            ->map(fn($item) => [
                'id'          => $item->id,
                'name'        => $item->name,
                'sku'         => $item->sku ?? '',
                'quantity'    => (float) $item->quantity,
                'unit'        => $item->unit,
                'branch_id'   => $item->branch_id,
                'branch_name' => null,
                'price'       => (float) $item->selling_price,
            ])
            ->toArray();

        $otherBranches = InventoryItem::where('vendor_id', $vendorId)
            ->whereNotNull('branch_id')
            ->where('branch_id', '!=', $branchId)
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%");
            })
            ->with('branch:id,name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'id'          => $item->id,
                'name'        => $item->name,
                'sku'         => $item->sku ?? '',
                'quantity'    => (float) $item->quantity,
                'unit'        => $item->unit,
                'branch_id'   => $item->branch_id,
                'branch_name' => $item->branch?->name ?? 'Other Branch',
                'price'       => (float) $item->selling_price,
            ])
            ->toArray();

        $this->itemSuggestions[$index] = [
            'my_branch'      => $myBranch,
            'other_branches' => $otherBranches,
        ];
    }

    public function selectInventoryItem(int $index, int $inventoryId): void
    {
        $item = InventoryItem::find($inventoryId);
        if (!$item || !isset($this->items[$index])) {
            return;
        }

        $this->items[$index]['description']       = $item->name;
        $this->items[$index]['inventory_item_id'] = $item->id;
        $this->items[$index]['unit_price']         = (float) $item->selling_price;
        $this->itemSuggestions[$index]            = ['my_branch' => [], 'other_branches' => []];
    }

    public function clearInventoryLink(int $index): void
    {
        if (!isset($this->items[$index])) return;
        $this->items[$index]['inventory_item_id'] = null;
        $this->itemSuggestions[$index] = ['my_branch' => [], 'other_branches' => []];
    }

    // ─── Add to inventory modal ──────────────────────────────────────────

    public function promptAddToInventory(int $index): void
    {
        $this->addToInventoryIndex = $index;
        $this->newItemName = $this->items[$index]['description'] ?? '';
        $this->newItemSku = '';
        $this->newItemCategoryId = null;
        $this->newItemSupplierId = null;
        $this->newItemUnit = 'pcs';
        $this->newItemCostPrice = 0;
        $this->newItemSellingPrice = (float) ($this->items[$index]['unit_price'] ?? 0);
        $this->showAddToInventoryModal = true;
    }

    public function saveAddToInventoryItem(): void
    {
        $this->validate([
            'newItemName'        => 'required|string|max:255',
            'newItemSku'         => 'nullable|string|max:100',
            'newItemCategoryId'  => 'nullable|integer|exists:inventory_categories,id',
            'newItemSupplierId'  => 'nullable|integer|exists:suppliers,id',
            'newItemUnit'        => 'required|string|max:20',
            'newItemCostPrice'   => 'required|numeric|min:0',
            'newItemSellingPrice'=> 'required|numeric|min:0',
        ]);

        $inventoryItem = InventoryItem::create([
            'vendor_id'     => auth()->user()->vendor_id,
            'branch_id'     => $this->workOrder->branch_id,
            'category_id'   => $this->newItemCategoryId,
            'supplier_id'   => $this->newItemSupplierId,
            'name'          => $this->newItemName,
            'sku'           => $this->newItemSku ?: null,
            'unit'          => $this->newItemUnit,
            'quantity'      => 0,
            'reorder_level' => 0,
            'cost_price'    => $this->newItemCostPrice,
            'selling_price' => $this->newItemSellingPrice,
            'is_active'     => true,
        ]);

        // Link to the row that triggered the modal
        if ($this->addToInventoryIndex !== null && isset($this->items[$this->addToInventoryIndex])) {
            $this->items[$this->addToInventoryIndex]['inventory_item_id'] = $inventoryItem->id;
            $this->items[$this->addToInventoryIndex]['description'] = $inventoryItem->name;
            $this->items[$this->addToInventoryIndex]['unit_price']  = (float) $inventoryItem->selling_price;
        }

        $this->showAddToInventoryModal = false;
        $this->addToInventoryIndex = null;

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Item added to inventory and linked.']);
    }

    public function closeAddToInventoryModal(): void
    {
        $this->showAddToInventoryModal = false;
        $this->addToInventoryIndex = null;
    }

    // ─── Computed totals ─────────────────────────────────────────────────

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(function ($item) {
            return max(0, ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0));
        });
    }

    public function getVatAmountProperty(): float
    {
        if ($this->vatRate <= 0) return 0;

        return collect($this->items)
            ->filter(fn($item) => (bool) ($item['vat_applicable'] ?? false))
            ->sum(function ($item) {
                $lineTotal = max(0, ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0));
                return $lineTotal * ($this->vatRate / 100);
            });
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->vatAmount;
    }

    // ─── Load helpers ─────────────────────────────────────────────────────

    protected function loadSuppliers(): void
    {
        $this->suppliers = Supplier::where('vendor_id', auth()->user()->vendor_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function getInventoryCategoriesProperty()
    {
        return InventoryCategory::where('vendor_id', auth()->user()->vendor_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // SAVE ACTIONS
    // ═══════════════════════════════════════════════════════════════════════

    protected function validateForm(): void
    {
        $this->validate([
            'items'                        => 'required|array|min:1',
            'items.*.item_type'            => 'required|in:labor,part',
            'items.*.description'          => 'required|string|max:255',
            'items.*.quantity'             => 'required|numeric|min:0.01',
            'items.*.unit_price'           => 'required|numeric|min:0',
            'items.*.discount'             => 'nullable|numeric|min:0',
            'validUntil'                   => 'required|date|after:today',
            'vatRate'                      => 'nullable|numeric|min:0|max:100',
        ], [
            'items.required'               => 'Add at least one line item.',
            'items.min'                    => 'Add at least one line item.',
            'validUntil.required'          => 'Valid-until date is required.',
            'validUntil.after'             => 'Valid-until must be a future date.',
        ]);
    }

    protected function persistQuotation(string $status): Quotation
    {
        return DB::transaction(function () use ($status) {
            // Determine revision version
            $version = 1;
            $parentId = null;

            if ($this->quotation) {
                // Editing draft: update in place
                $quotation = $this->quotation;
                $quotation->update([
                    'status'             => $status,
                    'notes'              => $this->notes,
                    'terms_and_conditions' => $this->termsAndConditions,
                    'valid_until'        => $this->validUntil,
                    'vat_rate'           => $this->vatRate,
                ]);
                $quotation->items()->delete();
            } else {
                // New quotation
                $existing = $this->workOrder->latestQuotation;
                if ($existing) {
                    $version  = $existing->version + 1;
                    $parentId = $existing->id;
                    // Mark old one superseded if not approved
                    if (!$existing->isApproved()) {
                        $existing->update(['status' => 'expired']);
                    }
                }

                $quotation = Quotation::create([
                    'branch_id'          => $this->workOrder->branch_id,
                    'work_order_id'      => $this->workOrder->id,
                    'customer_id'        => $this->workOrder->customer_id,
                    'created_by'         => auth()->id(),
                    'status'             => $status,
                    'version'            => $version,
                    'parent_quotation_id'=> $parentId,
                    'notes'              => $this->notes,
                    'terms_and_conditions' => $this->termsAndConditions,
                    'valid_until'        => $this->validUntil,
                    'vat_rate'           => $this->vatRate,
                ]);
            }

            // Persist line items
            foreach ($this->items as $sortOrder => $row) {
                $lineTotal = max(0, ($row['quantity'] * $row['unit_price']) - ($row['discount'] ?? 0));
                $vatAmt    = ($row['vat_applicable'] ?? false) ? $lineTotal * ($this->vatRate / 100) : 0;

                QuotationItem::create([
                    'quotation_id'      => $quotation->id,
                    'inventory_item_id' => $row['inventory_item_id'] ?? null,
                    'supplier_id'       => $row['supplier_id'] ?? null,
                    'item_type'         => $row['item_type'],
                    'description'       => $row['description'],
                    'quantity'          => $row['quantity'],
                    'unit_price'        => $row['unit_price'],
                    'discount'          => $row['discount'] ?? 0,
                    'vat_applicable'    => (bool) ($row['vat_applicable'] ?? false),
                    'vat_rate'          => $this->vatRate,
                    'total'             => $lineTotal,
                    'sort_order'        => $sortOrder,
                ]);
            }

            // Recalculate totals
            $quotation->recalculateTotals();

            // Mark work order as quoted
            $this->workOrder->update(['status' => 'quoted']);

            return $quotation;
        });
    }

    public function saveDraft(): void
    {
        $this->authorize('create_quotations');
        $this->validateForm();

        $quotation = $this->persistQuotation('draft');

        session()->flash('success', 'Quotation draft saved.');
        $this->redirect(route('quotations.show', $quotation), navigate: true);
    }

    public function saveAndSend(): void
    {
        $this->authorize('send_quotations');
        $this->validateForm();

        $quotation = $this->persistQuotation('sent');

        $quotation->update(['sent_at' => now()]);

        session()->flash('success', 'Quotation saved and marked as sent.');
        $this->redirect(route('quotations.show', $quotation), navigate: true);
    }

    // ─── Create revision ─────────────────────────────────────────────────

    public function createRevision(): void
    {
        $this->authorize('edit_quotations');

        if (!$this->quotation) return;

        $this->quotation = null; // force new creation → triggers version increment logic
        $this->saveDraft();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // RENDER
    // ═══════════════════════════════════════════════════════════════════════

    public function render()
    {
        return view('livewire.quotations.quotation-builder')
            ->layout('components.layouts.app', [
                'title' => $this->quotation
                    ? 'Edit Quotation ' . $this->quotation->quotation_number
                    : 'New Quotation — ' . $this->workOrder->order_number,
            ]);
    }
}
