<?php

namespace App\Domains\ServiceConfig\Livewire\Templates;

use App\Domains\ServiceConfig\Models\QualityCheckTemplate;
use App\Domains\ServiceConfig\Models\ServiceTemplate;
use App\Domains\ServiceConfig\Models\WashPackage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TemplatesComponent extends Component
{
    #[\Livewire\Attributes\Url]
    public $tab = 'service';

    public $showServiceModal = false;

    public $showWashModal = false;

    public $showQualityModal = false;

    // Service Template fields
    public $serviceName = '';

    public $serviceType = 'service';

    public $serviceDescription = '';

    public $serviceItems = [];

    // Wash Package fields
    public $washName = '';

    public $washType = 'basic';

    public $washPrice = 0;

    public $washDuration = 30;

    public $washServices = [];

    // Quality Checklist Template fields
    public $qualitySection = 'exterior';

    public $qualityCustomSection = '';

    public $qualityItemName = '';

    public $qualityCustomItemName = '';

    public $qualitySortOrder = 0;

    public function mount(): void
    {
        if (! in_array($this->tab, ['service', 'wash', 'quality'], true)) {
            $this->tab = 'service';
        }
    }

    public function updatedTab(): void
    {
        if (! in_array($this->tab, ['service', 'wash', 'quality'], true)) {
            $this->tab = 'service';
        }
    }

    public function openQualityModal(): void
    {
        $this->resetErrorBag();
        $this->showQualityModal = true;

        // Ensure dependent dropdown has a valid initial value.
        $this->qualitySection = 'exterior';
        $seededItems = $this->getSeededItemsForSection($this->qualitySection);
        $this->qualityItemName = $seededItems[0] ?? '__custom__';
        $this->qualityCustomSection = '';
        $this->qualityCustomItemName = '';
    }

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
            'serviceType' => 'required|in:service,repair,diagnostics,bodywork,electrical,ac,tyres,other',
            'serviceDescription' => 'nullable|string|max:2000',
            'serviceItems' => 'required|array|min:1|max:100',
            'serviceItems.*.item_type' => 'required|in:labor,part',
            'serviceItems.*.description' => 'required|string|max:255',
            'serviceItems.*.quantity' => 'required|numeric|gt:0',
            'serviceItems.*.unit_price' => 'required|numeric|min:0',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () {
            $template = ServiceTemplate::create([
                'vendor_id' => Auth::user()?->vendor_id,
                'name' => $this->serviceName,
                'category' => $this->serviceType,
                'description' => $this->serviceDescription,
                'is_active' => true,
            ]);

            foreach ($this->serviceItems as $item) {
                $template->items()->create(\Illuminate\Support\Arr::only($item, ['item_type', 'description', 'quantity', 'unit_price']));
            }
        });

        $this->reset(['serviceName', 'serviceType', 'serviceDescription', 'serviceItems', 'showServiceModal']);
        session()->flash('success', 'Service template created.');
    }

    public function addWashService()
    {
        $this->washServices[] = ['name' => ''];
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
            'washDuration' => 'required|integer|min:5',
            'washType' => 'required|in:basic,standard,premium,interior,exterior,engine,full_detail,custom',
            'washServices' => 'required|array|min:1|max:100',
            'washServices.*.name' => 'required|string|max:255',
        ]);

        WashPackage::create([
            'vendor_id' => Auth::user()?->vendor_id,
            'name' => $this->washName,
            'wash_type' => $this->washType,
            'price' => $this->washPrice,
            'estimated_duration_minutes' => $this->washDuration,
            'includes' => array_column($this->washServices, 'name'),
            'is_active' => true,
        ]);

        $this->reset(['washName', 'washType', 'washPrice', 'washDuration', 'washServices', 'showWashModal']);
        session()->flash('success', 'Wash package created.');
    }

    public function toggleServiceStatus(ServiceTemplate $template)
    {
        abort_unless($template->vendor_id === Auth::user()?->vendor_id, 403);
        $template->update(['is_active' => ! $template->is_active]);
    }

    public function toggleWashStatus(WashPackage $package)
    {
        abort_unless($package->vendor_id === Auth::user()?->vendor_id, 403);
        $package->update(['is_active' => ! $package->is_active]);
    }

    public function createQualityTemplate()
    {
        $this->validate([
            'qualitySection' => 'required|string|max:255',
            'qualityCustomSection' => 'nullable|string|max:255',
            'qualityItemName' => 'required|string|max:255',
            'qualityCustomItemName' => 'nullable|string|max:255',
            'qualitySortOrder' => 'nullable|integer|min:0',
        ]);

        $section = $this->qualitySection === '__custom__'
            ? trim((string) $this->qualityCustomSection)
            : $this->qualitySection;

        $itemName = $this->qualityItemName === '__custom__'
            ? trim((string) $this->qualityCustomItemName)
            : trim((string) $this->qualityItemName);

        if ($section === '') {
            $this->addError('qualityCustomSection', 'Please enter a custom section name.');

            return;
        }

        if ($itemName === '') {
            $this->addError('qualityCustomItemName', 'Please enter a custom checklist item name.');

            return;
        }

        QualityCheckTemplate::create([
            'vendor_id' => Auth::user()?->vendor_id,
            'section' => $section,
            'item_name' => $itemName,
            'sort_order' => $this->qualitySortOrder ?? 0,
            'is_active' => true,
            'is_default' => false,
        ]);

        $this->reset([
            'qualitySection',
            'qualityCustomSection',
            'qualityItemName',
            'qualityCustomItemName',
            'qualitySortOrder',
            'showQualityModal',
        ]);
        $this->qualitySection = 'exterior';
        $this->qualityItemName = '';
        $this->qualitySortOrder = 0;

        session()->flash('success', 'Quality checklist template item created.');
    }

    public function updatedQualitySection($value): void
    {
        if ($value === '__custom__') {
            $this->qualityItemName = '__custom__';

            return;
        }

        $seededItems = $this->getSeededItemsForSection($value);
        $this->qualityItemName = $seededItems[0] ?? '__custom__';
        $this->qualityCustomItemName = '';
    }

    public function updatedQualityCustomSection($value): void
    {
        if ($this->qualitySection !== '__custom__') {
            return;
        }

        $section = trim((string) $value);
        if ($section === '') {
            $this->qualityItemName = '__custom__';

            return;
        }

        $seededItems = $this->getSeededItemsForSection($section);
        $this->qualityItemName = $seededItems[0] ?? '__custom__';
    }

    public function toggleQualityStatus(QualityCheckTemplate $template)
    {
        if ($template->vendor_id !== Auth::user()?->vendor_id) {
            session()->flash('error', 'You can only update your own quality checklist template items.');

            return;
        }

        $template->update(['is_active' => ! $template->is_active]);
    }

    public function deleteQualityTemplate(QualityCheckTemplate $template)
    {
        if ($template->vendor_id !== Auth::user()?->vendor_id) {
            session()->flash('error', 'You can only delete your own quality checklist template items.');

            return;
        }

        $template->delete();
        session()->flash('success', 'Quality checklist template item deleted.');
    }

    public function getServiceTemplatesProperty()
    {
        return ServiceTemplate::with('items')->withCount('items')->orderBy('name')->get();
    }

    public function getWashPackagesProperty()
    {
        return WashPackage::orderBy('name')->get();
    }

    public function getQualityTemplatesProperty()
    {
        return QualityCheckTemplate::query()
            ->withoutVendorScope()
            ->where(function ($query) {
                $query->whereNull('vendor_id')
                    ->orWhere('vendor_id', Auth::user()?->vendor_id);
            })
            ->orderBy('section')
            ->orderBy('sort_order')
            ->orderBy('item_name')
            ->get();
    }

    public function getQualitySectionsProperty()
    {
        return QualityCheckTemplate::SECTIONS;
    }

    public function getAvailableQualitySectionsProperty()
    {
        $seededSections = QualityCheckTemplate::query()
            ->withoutVendorScope()
            ->whereNull('vendor_id')
            ->where('is_default', true)
            ->where('is_active', true)
            ->pluck('section')
            ->unique()
            ->values()
            ->all();

        $knownSections = array_keys(QualityCheckTemplate::SECTIONS);
        $sections = array_values(array_unique(array_merge($knownSections, $seededSections)));

        $result = [];
        foreach ($sections as $section) {
            $result[$section] = QualityCheckTemplate::SECTIONS[$section]
                ?? ucwords(str_replace('_', ' ', $section));
        }

        return $result;
    }

    public function getSeededQualityItemsProperty()
    {
        $section = $this->qualitySection === '__custom__'
            ? trim((string) $this->qualityCustomSection)
            : $this->qualitySection;

        if ($section === '') {
            return [];
        }

        return $this->getSeededItemsForSection($section);
    }

    protected function getSeededItemsForSection(string $section): array
    {
        return QualityCheckTemplate::query()
            ->withoutVendorScope()
            ->whereNull('vendor_id')
            ->where('is_default', true)
            ->where('is_active', true)
            ->where('section', $section)
            ->orderBy('sort_order')
            ->pluck('item_name')
            ->unique()
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.templates.templates-component')
            ->layout('components.layouts.app', ['title' => 'Service Templates']);
    }
}
