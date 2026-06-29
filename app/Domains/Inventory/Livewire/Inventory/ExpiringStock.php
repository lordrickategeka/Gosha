<?php

namespace App\Domains\Inventory\Livewire\Inventory;

use App\Domains\Inventory\Models\InventoryItem;
use Livewire\Component;
use Livewire\WithPagination;

class ExpiringStock extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'expiring'; // expiring, expired, all
    public int $daysThreshold = 30; // Days for "expiring soon"

    protected $queryString = [
        'filter' => ['except' => 'expiring'],
        'daysThreshold' => ['except' => 30],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setFilter(string $filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function markAsWastage(int $itemId)
    {
        $item = InventoryItem::findOrFail($itemId);

        if (!$item->isExpired()) {
            session()->flash('error', 'Item is not expired yet.');
            return;
        }

        try {
            // Create wastage movement
            $item->movements()->create([
                'vendor_id' => $item->vendor_id,
                'branch_id' => $item->branch_id,
                'movement_type' => 'wastage',
                'quantity_change' => -$item->quantity,
                'quantity_after' => 0,
                'unit_cost' => $item->cost_price,
                'total_cost' => $item->quantity * $item->cost_price,
                'performed_by' => auth()->id(),
                'notes' => 'Expired - marked as wastage on ' . now()->format('Y-m-d'),
                'movement_date' => now(),
            ]);

            // Set quantity to 0
            $item->update(['quantity' => 0]);

            session()->flash('success', 'Expired item marked as wastage.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to mark as wastage: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $branchId = session('current_branch_id');

        $branchScope = fn($q) => $q->where(function ($s) use ($branchId) {
            $s->where('branch_id', $branchId)->orWhereNull('branch_id');
        });

        $query = InventoryItem::with(['category', 'supplier', 'branch'])
            ->tap($branchScope)
            ->whereNotNull('expiry_date')
            ->when($this->search, fn($q) => $q->where(function ($s) {
                $s->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            }));

        // Apply filter
        if ($this->filter === 'expired') {
            $query->expired();
        } elseif ($this->filter === 'expiring') {
            $query->expiringSoon($this->daysThreshold);
        }
        // 'all' shows everything with expiry dates

        $items = $query->orderBy('expiry_date', 'asc')
            ->paginate(20);

        $stats = [
            'expired' => InventoryItem::tap($branchScope)->expired()->count(),
            'expiring_7_days' => InventoryItem::tap($branchScope)->expiringSoon(7)->count(),
            'expiring_30_days' => InventoryItem::tap($branchScope)->expiringSoon(30)->count(),
            'total_with_expiry' => InventoryItem::tap($branchScope)->whereNotNull('expiry_date')->count(),
            'expired_value' => InventoryItem::tap($branchScope)
                ->expired()
                ->selectRaw('SUM(quantity * cost_price) as total')
                ->value('total') ?? 0,
        ];

        return view('livewire.inventory.expiring-stock', compact('items', 'stats'))
            ->layout('components.layouts.app', ['title' => 'Expiring Stock']);
    }
}
