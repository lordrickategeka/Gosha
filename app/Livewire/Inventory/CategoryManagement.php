<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryCategory;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = ''; // service_parts, wash_supplies

    // Create/Edit modal
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $type = 'service_parts';
    public string $code = '';
    public string $description = '';
    public string $parent_id = '';

    // Delete confirmation
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:inventory_categories,name,' . $this->editingId . ',id,vendor_id,' . auth()->user()->vendor_id . ',parent_id,' . ($this->parent_id ?: 'NULL')
            ],
            'type' => 'required|in:service_parts,wash_supplies',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:inventory_categories,id',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['editingId', 'name', 'type', 'code', 'description', 'parent_id']);
        $this->type = 'service_parts';
        $this->showModal = true;
    }

    public function openEditModal(int $categoryId)
    {
        $category = InventoryCategory::findOrFail($categoryId);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->type = $category->type;
        $this->code = $category->code ?? '';
        $this->description = $category->description ?? '';
        $this->parent_id = $category->parent_id ?? '';

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'vendor_id' => auth()->user()->vendor_id,
            'name' => $this->name,
            'type' => $this->type,
            'code' => $this->code ?: null,
            'description' => $this->description ?: null,
            'parent_id' => $this->parent_id ?: null,
            'is_active' => true,
        ];

        if ($this->editingId) {
            InventoryCategory::findOrFail($this->editingId)->update($data);
            $message = 'Category updated successfully.';
        } else {
            InventoryCategory::create($data);
            $message = 'Category created successfully.';
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'type', 'code', 'description', 'parent_id']);

        session()->flash('success', $message);
    }

    public function confirmDelete(int $categoryId)
    {
        $this->deletingId = $categoryId;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if (!$this->deletingId) {
            return;
        }

        $category = InventoryCategory::findOrFail($this->deletingId);

        // Check if category has items
        if ($category->items()->exists()) {
            $this->addError('delete', 'Cannot delete category with inventory items. Please reassign items first.');
            return;
        }

        // Check if category has children
        if ($category->children()->exists()) {
            $this->addError('delete', 'Cannot delete category with subcategories. Please delete subcategories first.');
            return;
        }

        $category->delete();

        $this->showDeleteModal = false;
        $this->deletingId = null;

        session()->flash('success', 'Category deleted successfully.');
    }

    public function toggleActive(int $categoryId)
    {
        $category = InventoryCategory::findOrFail($categoryId);
        $category->update(['is_active' => !$category->is_active]);

        session()->flash('success', 'Category status updated.');
    }

    public function getParentCategoriesProperty()
    {
        return InventoryCategory::whereNull('parent_id')
            ->where('type', $this->type)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        $categories = InventoryCategory::with(['parent', 'children', 'items'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(20);

        $stats = [
            'total' => InventoryCategory::count(),
            'service_parts' => InventoryCategory::where('type', 'service_parts')->count(),
            'wash_supplies' => InventoryCategory::where('type', 'wash_supplies')->count(),
            'root_categories' => InventoryCategory::whereNull('parent_id')->count(),
        ];

        return view('livewire.inventory.category-management', compact('categories', 'stats'))
            ->layout('components.layouts.app', ['title' => 'Inventory Categories']);
    }
}
