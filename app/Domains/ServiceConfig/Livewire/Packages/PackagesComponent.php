<?php

namespace App\Domains\ServiceConfig\Livewire\Packages;

use App\Domains\ServiceConfig\Models\WashPackage;
use Livewire\Component;
use Livewire\WithPagination;

class PackagesComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = '';
    public $filterStatus = '';

    // Modal state
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $name = '';
    public $wash_type = 'basic';
    public $description = '';
    public $includes = [];
    public $newIncludeItem = '';
    public $estimated_duration_minutes = 30;
    public $price = 0;
    public $is_active = true;
    public $sort_order = 0;

    protected $rules = [
        'name'                       => 'required|string|max:255',
        'wash_type'                  => 'required|in:basic,standard,premium,interior,exterior,engine,full_detail,custom',
        'description'                => 'nullable|string|max:1000',
        'includes'                   => 'array',
        'estimated_duration_minutes' => 'required|integer|min:5',
        'price'                      => 'required|numeric|min:0',
        'sort_order'                 => 'nullable|integer|min:0',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    // ── Includes list helpers ─────────────────────────────────────────────────

    public function addIncludeItem()
    {
        $item = trim($this->newIncludeItem);
        if ($item && !in_array($item, $this->includes)) {
            $this->includes[] = $item;
        }
        $this->newIncludeItem = '';
    }

    public function removeIncludeItem($index)
    {
        unset($this->includes[$index]);
        $this->includes = array_values($this->includes);
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function create()
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $package = WashPackage::where('vendor_id', auth()->user()->vendor_id)->findOrFail($id);
        $this->editingId = $package->id;
        $this->name = $package->name;
        $this->wash_type = $package->wash_type;
        $this->description = $package->description ?? '';
        $this->includes = $package->includes ?? [];
        $this->estimated_duration_minutes = $package->estimated_duration_minutes;
        $this->price = $package->price;
        $this->is_active = $package->is_active;
        $this->sort_order = $package->sort_order;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name'                       => $this->name,
            'wash_type'                  => $this->wash_type,
            'description'                => $this->description ?: null,
            'includes'                   => array_values($this->includes),
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'price'                      => $this->price,
            'is_active'                  => $this->is_active,
            'sort_order'                 => $this->sort_order ?? 0,
        ];

        if ($this->editingId) {
            WashPackage::where('vendor_id', auth()->user()->vendor_id)
                ->findOrFail($this->editingId)
                ->update($data);
            session()->flash('success', 'Package updated successfully.');
        } else {
            WashPackage::create(array_merge($data, [
                'vendor_id' => auth()->user()->vendor_id,
            ]));
            session()->flash('success', 'Package created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleStatus($id)
    {
        $package = WashPackage::where('vendor_id', auth()->user()->vendor_id)->findOrFail($id);
        $package->update(['is_active' => !$package->is_active]);
    }

    public function delete($id)
    {
        $package = WashPackage::where('vendor_id', auth()->user()->vendor_id)->findOrFail($id);
        $package->delete();
        session()->flash('success', 'Package deleted.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->reset([
            'editingId', 'name', 'wash_type', 'description', 'includes',
            'newIncludeItem', 'estimated_duration_minutes', 'price',
            'is_active', 'sort_order',
        ]);
        $this->estimated_duration_minutes = 30;
        $this->is_active = true;
        $this->wash_type = 'basic';
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getPackagesProperty()
    {
        $vendorId = auth()->user()->vendor_id;
        return WashPackage::where('vendor_id', $vendorId)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            }))
            ->when($this->filterType, fn($q) => $q->where('wash_type', $this->filterType))
            ->when($this->filterStatus !== '', fn($q) => $q->where('is_active', (bool) $this->filterStatus))
            ->orderBy('sort_order')
            ->orderBy('price')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.packages.packages-component', [
            'packages' => $this->packages,
        ])->layout('components.layouts.app', ['title' => 'Wash Packages']);
    }
}
