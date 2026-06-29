<?php

namespace App\Domains\ServiceConfig\Livewire;

use App\Domains\ServiceConfig\Models\ServiceCategory;
use Livewire\Component;

class ServiceCategoriesComponent extends Component
{
    public $serviceCategories;

    public string $name = '';

    public ?string $description = null;

    public bool $is_active = true;

    public bool $editMode = false;

    public bool $showFormModal = false;

    public ?int $serviceCategoryId = null;

    protected function rules(): array
    {
        $categoryId = $this->serviceCategoryId;

        return [
            'name' => 'required|string|max:255|unique:service_categories,name,' . $categoryId,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function mount(): void
    {
        $this->refreshServiceCategories();
    }

    public function refreshServiceCategories(): void
    {
        $this->serviceCategories = ServiceCategory::query()
            ->orderBy('name')
            ->get();
    }

    public function create(): void
    {
        $validated = $this->validate();

        ServiceCategory::create($validated);

        $this->resetForm();
        $this->refreshServiceCategories();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Service category created successfully.']);
    }

    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function edit(int $id): void
    {
        $category = ServiceCategory::findOrFail($id);

        $this->serviceCategoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->is_active = $category->is_active;
        $this->editMode = true;
        $this->showFormModal = true;
    }

    public function update(): void
    {
        $validated = $this->validate();

        $category = ServiceCategory::findOrFail($this->serviceCategoryId);
        $category->update($validated);

        $this->resetForm();
        $this->refreshServiceCategories();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Service category updated successfully.']);
    }

    public function confirmDelete(int $id): void
    {
        $this->serviceCategoryId = $id;
    }

    public function delete(): void
    {
        if (! $this->serviceCategoryId) {
            return;
        }

        ServiceCategory::destroy($this->serviceCategoryId);

        $this->resetForm();
        $this->refreshServiceCategories();

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Service category deleted successfully.']);
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'description', 'editMode', 'serviceCategoryId', 'showFormModal']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.service-categories-component', [
            'serviceCategories' => $this->serviceCategories,
        ])->layout('components.layouts.app', ['title' => 'Service Categories']);
    }
}
