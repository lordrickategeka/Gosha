<?php

namespace App\Livewire\Settings;

use App\Helpers\NumberGeneratorHelper;
use App\Models\InventoryCategory;
use App\Models\InventoryType;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TaxonomyComponent extends Component
{
    use WithPagination;

    public string $section = 'inventory_categories';

    public ?int $inventoryCategoryId = null;
    public string $inventoryCategoryName = '';
    public string $inventoryCategoryType = 'service_parts';
    public string $inventoryCategoryCode = '';
    public string $inventoryCategoryDescription = '';
    public ?int $inventoryCategoryParentId = null;
    public bool $inventoryCategoryIsActive = true;

    public ?int $inventoryTypeId = null;
    public string $inventoryTypeName = '';
    public ?int $inventoryTypeCategoryId = null;

    public ?int $serviceTypeId = null;
    public string $serviceTypeName = '';
    public float $serviceTypePrice = 0;
    public int $serviceTypeEstimatedDuration = 30;

    public ?int $serviceCategoryId = null;
    public string $serviceCategoryName = '';
    public string $serviceCategoryDescription = '';
    public bool $serviceCategoryIsActive = true;

    public function setSection(string $section): void
    {
        $allowed = ['inventory_categories', 'inventory_types', 'service_types', 'service_categories'];

        if (! in_array($section, $allowed, true)) {
            return;
        }

        $this->section = $section;
        $this->resetPage();
        $this->resetValidation();
    }

    public function updatedInventoryCategoryName(): void
    {
        $this->suggestInventoryCategoryCode();
    }

    public function updatedInventoryCategoryType(): void
    {
        $this->inventoryCategoryCode = '';
        $this->inventoryCategoryParentId = null;
        $this->suggestInventoryCategoryCode();
    }

    protected function inventoryCategoryRules(): array
    {
        $vendorId = (int) auth()->user()->vendor_id;

        return [
            'inventoryCategoryName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inventory_categories', 'name')
                    ->ignore($this->inventoryCategoryId)
                    ->where(function ($query) use ($vendorId) {
                        $query->where('vendor_id', $vendorId);

                        if ($this->inventoryCategoryParentId) {
                            $query->where('parent_id', $this->inventoryCategoryParentId);
                        } else {
                            $query->whereNull('parent_id');
                        }
                    }),
            ],
            'inventoryCategoryType' => 'required|in:service_parts,wash_supplies,consumables,tools',
            'inventoryCategoryCode' => 'nullable|string|max:20',
            'inventoryCategoryDescription' => 'nullable|string|max:500',
            'inventoryCategoryParentId' => 'nullable|exists:inventory_categories,id',
            'inventoryCategoryIsActive' => 'boolean',
        ];
    }

    public function openInventoryCategoryCreate(): void
    {
        $this->resetInventoryCategoryForm();
        $this->suggestInventoryCategoryCode();
    }

    public function editInventoryCategory(int $id): void
    {
        $category = InventoryCategory::findOrFail($id);

        $this->inventoryCategoryId = $category->id;
        $this->inventoryCategoryName = $category->name;
        $this->inventoryCategoryType = $category->type;
        $this->inventoryCategoryCode = $category->code ?? '';
        $this->inventoryCategoryDescription = $category->description ?? '';
        $this->inventoryCategoryParentId = $category->parent_id;
        $this->inventoryCategoryIsActive = (bool) $category->is_active;
    }

    public function saveInventoryCategory(): void
    {
        $validated = $this->validate($this->inventoryCategoryRules());

        $data = [
            'vendor_id' => auth()->user()->vendor_id,
            'parent_id' => $validated['inventoryCategoryParentId'] ?? null,
            'name' => $validated['inventoryCategoryName'],
            'type' => $validated['inventoryCategoryType'],
            'code' => $validated['inventoryCategoryCode'] ?: null,
            'description' => $validated['inventoryCategoryDescription'] ?: null,
            'is_active' => $validated['inventoryCategoryIsActive'],
        ];

        if ($this->inventoryCategoryId) {
            InventoryCategory::findOrFail($this->inventoryCategoryId)->update($data);
            $message = 'Inventory category updated successfully.';
        } else {
            InventoryCategory::create($data);
            $message = 'Inventory category created successfully.';
        }

        $this->resetInventoryCategoryForm();
        session()->flash('message', $message);
    }

    public function deleteInventoryCategory(int $id): void
    {
        $category = InventoryCategory::findOrFail($id);

        if ($category->items()->exists()) {
            session()->flash('message', 'Remove or reassign inventory items before deleting this category.');
            return;
        }

        if ($category->children()->exists()) {
            session()->flash('message', 'Delete child categories before removing this category.');
            return;
        }

        $category->delete();

        session()->flash('message', 'Inventory category deleted successfully.');
    }

    protected function suggestInventoryCategoryCode(): void
    {
        if ($this->inventoryCategoryCode !== '' || ! $this->inventoryCategoryName) {
            return;
        }

        $vendorId = (int) auth()->user()->vendor_id;

        if (! $vendorId) {
            return;
        }

        $this->inventoryCategoryCode = NumberGeneratorHelper::generateInventoryCategoryCode(
            $this->inventoryCategoryName,
            $this->inventoryCategoryType,
            $vendorId,
            $this->inventoryCategoryId,
        );
    }

    protected function resetInventoryCategoryForm(): void
    {
        $this->reset([
            'inventoryCategoryId',
            'inventoryCategoryName',
            'inventoryCategoryCode',
            'inventoryCategoryDescription',
            'inventoryCategoryParentId',
        ]);

        $this->inventoryCategoryType = 'service_parts';
        $this->inventoryCategoryIsActive = true;
        $this->resetValidation();
    }

    public function updatedInventoryTypeCategoryId(): void
    {
        // Keep the form tidy if the category changes while creating.
    }

    protected function inventoryTypeRules(): array
    {
        return [
            'inventoryTypeName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('inventory_types', 'name')
                    ->ignore($this->inventoryTypeId)
                    ->where(fn ($query) => $query->where('inventory_category_id', $this->inventoryTypeCategoryId)),
            ],
            'inventoryTypeCategoryId' => 'required|exists:inventory_categories,id',
        ];
    }

    public function openInventoryTypeCreate(): void
    {
        $this->resetInventoryTypeForm();
    }

    public function editInventoryType(int $id): void
    {
        $type = InventoryType::findOrFail($id);

        $this->inventoryTypeId = $type->id;
        $this->inventoryTypeName = $type->name;
        $this->inventoryTypeCategoryId = $type->inventory_category_id;
    }

    public function saveInventoryType(): void
    {
        $validated = $this->validate($this->inventoryTypeRules());

        $data = [
            'name' => $validated['inventoryTypeName'],
            'inventory_category_id' => $validated['inventoryTypeCategoryId'],
        ];

        if ($this->inventoryTypeId) {
            InventoryType::findOrFail($this->inventoryTypeId)->update($data);
            $message = 'Inventory type updated successfully.';
        } else {
            InventoryType::create($data);
            $message = 'Inventory type created successfully.';
        }

        $this->resetInventoryTypeForm();
        session()->flash('message', $message);
    }

    public function deleteInventoryType(int $id): void
    {
        InventoryType::findOrFail($id)->delete();
        session()->flash('message', 'Inventory type deleted successfully.');
    }

    protected function resetInventoryTypeForm(): void
    {
        $this->reset(['inventoryTypeId', 'inventoryTypeName', 'inventoryTypeCategoryId']);
        $this->resetValidation();
    }

    protected function serviceTypeRules(): array
    {
        return [
            'serviceTypeName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service_types', 'name')->ignore($this->serviceTypeId),
            ],
            'serviceTypePrice' => 'required|numeric|min:0',
            'serviceTypeEstimatedDuration' => 'required|integer|min:1',
        ];
    }

    public function openServiceTypeCreate(): void
    {
        $this->resetServiceTypeForm();
    }

    public function editServiceType(int $id): void
    {
        $type = ServiceType::findOrFail($id);

        $this->serviceTypeId = $type->id;
        $this->serviceTypeName = $type->name;
        $this->serviceTypePrice = (float) $type->price;
        $this->serviceTypeEstimatedDuration = (int) $type->estimated_duration;
    }

    public function saveServiceType(): void
    {
        $validated = $this->validate($this->serviceTypeRules());

        $data = [
            'name' => $validated['serviceTypeName'],
            'price' => $validated['serviceTypePrice'],
            'estimated_duration' => $validated['serviceTypeEstimatedDuration'],
        ];

        if ($this->serviceTypeId) {
            ServiceType::findOrFail($this->serviceTypeId)->update($data);
            $message = 'Service type updated successfully.';
        } else {
            ServiceType::create($data);
            $message = 'Service type created successfully.';
        }

        $this->resetServiceTypeForm();
        session()->flash('message', $message);
    }

    public function deleteServiceType(int $id): void
    {
        ServiceType::findOrFail($id)->delete();
        session()->flash('message', 'Service type deleted successfully.');
    }

    protected function resetServiceTypeForm(): void
    {
        $this->reset(['serviceTypeId', 'serviceTypeName']);
        $this->serviceTypePrice = 0;
        $this->serviceTypeEstimatedDuration = 30;
        $this->resetValidation();
    }

    protected function serviceCategoryRules(): array
    {
        return [
            'serviceCategoryName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service_categories', 'name')->ignore($this->serviceCategoryId),
            ],
            'serviceCategoryDescription' => 'nullable|string',
            'serviceCategoryIsActive' => 'boolean',
        ];
    }

    public function openServiceCategoryCreate(): void
    {
        $this->resetServiceCategoryForm();
    }

    public function editServiceCategory(int $id): void
    {
        $category = ServiceCategory::findOrFail($id);

        $this->serviceCategoryId = $category->id;
        $this->serviceCategoryName = $category->name;
        $this->serviceCategoryDescription = $category->description ?? '';
        $this->serviceCategoryIsActive = (bool) $category->is_active;
    }

    public function saveServiceCategory(): void
    {
        $validated = $this->validate($this->serviceCategoryRules());

        $data = [
            'name' => $validated['serviceCategoryName'],
            'description' => $validated['serviceCategoryDescription'] ?: null,
            'is_active' => $validated['serviceCategoryIsActive'],
        ];

        if ($this->serviceCategoryId) {
            ServiceCategory::findOrFail($this->serviceCategoryId)->update($data);
            $message = 'Service category updated successfully.';
        } else {
            ServiceCategory::create($data);
            $message = 'Service category created successfully.';
        }

        $this->resetServiceCategoryForm();
        session()->flash('message', $message);
    }

    public function deleteServiceCategory(int $id): void
    {
        ServiceCategory::findOrFail($id)->delete();
        session()->flash('message', 'Service category deleted successfully.');
    }

    protected function resetServiceCategoryForm(): void
    {
        $this->reset(['serviceCategoryId', 'serviceCategoryName', 'serviceCategoryDescription']);
        $this->serviceCategoryIsActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        $vendorId = auth()->user()->vendor_id;

        return view('livewire.settings.taxonomy-component', [
            'inventoryCategories' => InventoryCategory::with('parent')
                ->where('vendor_id', $vendorId)
                ->latest()
                ->paginate(10),
            'inventoryTypes' => InventoryType::with('category')
                ->orderBy('name')
                ->get(),
            'serviceTypes' => ServiceType::orderBy('name')->get(),
            'serviceCategories' => ServiceCategory::orderBy('name')->get(),
            'inventoryCategoryParents' => InventoryCategory::where('vendor_id', $vendorId)
                ->where('type', $this->inventoryCategoryType)
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(),
            'inventoryTypeCategories' => InventoryCategory::where('vendor_id', $vendorId)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
