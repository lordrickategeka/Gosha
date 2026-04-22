<?php

namespace App\Livewire\Templates;

namespace App\Livewire\Templates;
use Livewire\Component;
use App\Models\ServiceTemplate;
use App\Models\WashPackage;

class TemplatesComponent extends Component
{
    public $tab = 'service';
    public $showServiceModal = false;
    public $showWashModal = false;

    // Service Template fields
    public $serviceName = '';
    public $serviceType = 'service';
    public $serviceDescription = '';
    public $serviceItems = [];

    // Wash Package fields
    public $washName = '';
    public $washType = 'basic';
    public $washPrice = 0;
    public $washServices = [];

    public function addServiceItem()
    {
        $this->serviceItems[] = ['item_type' => 'labor', 'description' => '', 'quantity' => 1, 'unit_price' => 0];
    }

    public function removeServiceItem($index)
    {
        unset($this->serviceItems[$index]);
        $this->serviceItems = array_values($this->serviceItems);
    }

    public function createServiceTemplate()
    {
        $this->validate([
            'serviceName' => 'required|string|max:255',
            'serviceType' => 'required',
        ]);

        $template = ServiceTemplate::create([
            'vendor_id' => auth()->user()->vendor_id,
            'name' => $this->serviceName,
            'type' => $this->serviceType,
            'description' => $this->serviceDescription,
            'is_active' => true,
        ]);

        foreach ($this->serviceItems as $item) {
            $template->items()->create($item);
        }

        $this->reset(['serviceName', 'serviceType', 'serviceDescription', 'serviceItems', 'showServiceModal']);
        session()->flash('success', 'Service template created.');
    }

    public function addWashService()
    {
        $this->washServices[] = ['name' => '', 'price' => 0];
    }

    public function removeWashService($index)
    {
        unset($this->washServices[$index]);
        $this->washServices = array_values($this->washServices);
    }

    public function createWashPackage()
    {
        $this->validate([
            'washName' => 'required|string|max:255',
            'washPrice' => 'required|numeric|min:0',
        ]);

        WashPackage::create([
            'vendor_id' => auth()->user()->vendor_id,
            'name' => $this->washName,
            'wash_type' => $this->washType,
            'price' => $this->washPrice,
            'services' => $this->washServices,
            'is_active' => true,
        ]);

        $this->reset(['washName', 'washType', 'washPrice', 'washServices', 'showWashModal']);
        session()->flash('success', 'Wash package created.');
    }

    public function toggleServiceStatus(ServiceTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);
    }

    public function toggleWashStatus(WashPackage $package)
    {
        $package->update(['is_active' => !$package->is_active]);
    }

    public function getServiceTemplatesProperty()
    {
        return ServiceTemplate::withCount('items')->orderBy('name')->get();
    }

    public function getWashPackagesProperty()
    {
        return WashPackage::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.templates.templates-component')
            ->layout('components.layouts.app', ['title' => 'Service Templates']);
    }
}
