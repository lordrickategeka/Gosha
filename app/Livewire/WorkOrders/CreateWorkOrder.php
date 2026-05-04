<?php

namespace App\Livewire\WorkOrders;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\ServiceBay;
use App\Models\ServiceTemplate;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateWorkOrder extends Component
{
    use WithFileUploads;

    // ═══════════════════════════════════════════════════════════════════════
    // WIZARD STATE
    // ═══════════════════════════════════════════════════════════════════════

    public int $currentStep = 1;
    public int $totalSteps = 4;

    // ═══════════════════════════════════════════════════════════════════════
    // STEP 1: CUSTOMER & VEHICLE
    // ═══════════════════════════════════════════════════════════════════════

    public $customer_id = null;
    public $vehicle_id = null;
    public $customer_notes = '';

    // Customer search/list
    public $customerSearch = '';
    public $customers = [];
    public $vehicles = [];

    // Quick-add customer modal
    public $showCustomerModal = false;
    public $newCustomerName = '';
    public $newCustomerPhone = '';
    public $newCustomerEmail = '';

    // Quick-add vehicle modal
    public $showVehicleModal = false;
    public $newVehicleRegNumber = '';
    public $newVehicleMake = '';
    public $newVehicleModel = '';
    public $newVehicleYear = '';

    // ═══════════════════════════════════════════════════════════════════════
    // STEP 2: JOB DETAILS
    // ═══════════════════════════════════════════════════════════════════════

    public $type = 'service';
    public $priority = 'normal';
    public $service_bay_id = null;
    public $assigned_technician_id = null;
    public $is_combo = false;
    public $mileage_in = null;
    public $estimated_completion = null;

    // ═══════════════════════════════════════════════════════════════════════
    // STEP 3: LINE ITEMS
    // ═══════════════════════════════════════════════════════════════════════

    public $items = [];
    public $selectedTemplate = null;

    // ═══════════════════════════════════════════════════════════════════════
    // LIFECYCLE
    // ═══════════════════════════════════════════════════════════════════════

    public function mount()
    {
        // Validate session has branch
        if (!session('current_branch_id')) {
            session()->flash('error', 'No branch selected. Please select a branch first.');
            return redirect()->route('dashboard');
        }

        // Load initial customers
        $this->loadCustomers();

        Log::info('CreateWorkOrder component mounted', [
            'branch_id' => session('current_branch_id'),
            'vendor_id' => auth()->user()->vendor_id,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // WIZARD NAVIGATION
    // ═══════════════════════════════════════════════════════════════════════

    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep($step)
    {
        if ($step >= 1 && $step <= $this->totalSteps && $step <= $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // VALIDATION
    // ═══════════════════════════════════════════════════════════════════════

    protected function validateCurrentStep(): void
    {
        match ($this->currentStep) {
            1 => $this->validateStep1(),
            2 => $this->validateStep2(),
            3 => $this->validateStep3(),
            default => null,
        };
    }

    protected function validateStep1(): void
    {
        $this->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'vehicle_id' => 'required|integer|exists:vehicles,id',
        ], [
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'The selected customer does not exist.',
            'vehicle_id.required' => 'Please select a vehicle.',
            'vehicle_id.exists' => 'The selected vehicle does not exist.',
        ]);
    }

    protected function validateStep2(): void
    {
        $this->validate([
            'type' => 'required|in:repair,service,diagnostics,bodywork,electrical,ac,tyres,other',
            'priority' => 'required|in:low,normal,high,urgent',
            'service_bay_id' => 'nullable|integer|exists:service_bays,id',
            'assigned_technician_id' => 'nullable|integer|exists:users,id',
            'mileage_in' => 'nullable|integer|min:0',
            'estimated_completion' => 'nullable|date',
        ]);
    }

    protected function validateStep3(): void
    {
        $this->validate([
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:labor,part',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ], [
            'items.required' => 'Please add at least one item.',
            'items.min' => 'Please add at least one item.',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // STEP 1: CUSTOMER & VEHICLE METHODS
    // ═══════════════════════════════════════════════════════════════════════

    public function updatedCustomerSearch()
    {
        $this->loadCustomers();
    }

    public function updatedCustomerId($value)
    {
        if ($value) {
            $this->loadVehicles();
            // Reset vehicle selection when customer changes
            $this->vehicle_id = null;
        } else {
            $this->vehicles = [];
            $this->vehicle_id = null;
        }
    }

    protected function loadCustomers()
    {
        $branchId = session('current_branch_id');
        $query = Customer::where('vendor_id', auth()->user()->vendor_id)

             ->when($branchId, function ($q) use ($branchId) {
                $q->where(function ($sub) use ($branchId) {
                    $sub->whereHas('workOrders', fn($wq) => $wq->where('branch_id', $branchId))
                        ->orWhereHas('washOrders', fn($wq) => $wq->where('branch_id', $branchId));
                });
            });

        if (strlen($this->customerSearch) > 0) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->customerSearch . '%')
                    ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->customerSearch . '%');
            });
        }

        $this->customers = $query->orderBy('name')->limit(100)->get();
    }

    protected function loadVehicles()
    {
        if (!$this->customer_id) {
            $this->vehicles = [];
            return;
        }

        $this->vehicles = Vehicle::where('customer_id', $this->customer_id)
            ->where('is_active', true)
            ->orderBy('registration_number')
            ->get();
            
    }

    // Quick-add customer
    public function openCustomerModal()
    {
        $this->showCustomerModal = true;
        $this->resetCustomerModalFields();
    }

    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
        $this->resetCustomerModalFields();
    }

    public function saveNewCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|string|max:255',
            'newCustomerPhone' => 'required|string|max:20',
            'newCustomerEmail' => 'nullable|email|max:255',
        ]);

        $customer = Customer::create([
            'vendor_id' => auth()->user()->vendor_id,
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone,
            'email' => $this->newCustomerEmail,
            'is_active' => true,
        ]);

        $this->customer_id = $customer->id;
        $this->loadCustomers();
        $this->loadVehicles();
        $this->closeCustomerModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Customer created successfully.'
        ]);
    }

    protected function resetCustomerModalFields()
    {
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
        $this->resetValidation(['newCustomerName', 'newCustomerPhone', 'newCustomerEmail']);
    }

    // Quick-add vehicle
    public function openVehicleModal()
    {
        if (!$this->customer_id) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select a customer first.'
            ]);
            return;
        }

        $this->showVehicleModal = true;
        $this->resetVehicleModalFields();
    }

    public function closeVehicleModal()
    {
        $this->showVehicleModal = false;
        $this->resetVehicleModalFields();
    }

    public function saveNewVehicle()
    {
        $this->validate([
            'newVehicleRegNumber' => 'required|string|max:20|unique:vehicles,registration_number',
            'newVehicleMake' => 'nullable|string|max:50',
            'newVehicleModel' => 'nullable|string|max:50',
            'newVehicleYear' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        $vehicle = Vehicle::create([
            'customer_id' => $this->customer_id,
            'registration_number' => strtoupper($this->newVehicleRegNumber),
            'make' => $this->newVehicleMake,
            'model' => $this->newVehicleModel,
            'year' => $this->newVehicleYear,
            'is_active' => true,
        ]);

        $this->vehicle_id = $vehicle->id;
        $this->loadVehicles();
        $this->closeVehicleModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Vehicle added successfully.'
        ]);
    }

    protected function resetVehicleModalFields()
    {
        $this->newVehicleRegNumber = '';
        $this->newVehicleMake = '';
        $this->newVehicleModel = '';
        $this->newVehicleYear = '';
        $this->resetValidation(['newVehicleRegNumber', 'newVehicleMake', 'newVehicleModel', 'newVehicleYear']);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // STEP 2: JOB DETAILS - COMPUTED PROPERTIES
    // ═══════════════════════════════════════════════════════════════════════

    public function getServiceBaysProperty()
    {
        return ServiceBay::where('branch_id', session('current_branch_id'))
            ->where('status', 'available')
            ->orderBy('name')
            ->get();
    }

    public function getTechniciansProperty()
    {
        return User::role('technician')
            ->where('is_active', true)
            ->where('vendor_id', auth()->user()->vendor_id)
            ->whereHas('branches', fn($q) => $q->where('branch_id', session('current_branch_id')))
            ->orderBy('name')
            ->get();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // STEP 3: LINE ITEMS METHODS
    // ═══════════════════════════════════════════════════════════════════════

    public function addItem($type = 'labor')
    {
        $this->items[] = [
            'item_type' => $type,
            'description' => '',
            'inventory_item_id' => null,
            'quantity' => 1,
            'unit_price' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function applyTemplate()
    {
        if (!$this->selectedTemplate) {
            return;
        }

        $template = ServiceTemplate::with('items')->find($this->selectedTemplate);

        if (!$template) {
            return;
        }

        foreach ($template->items as $item) {
            $this->items[] = [
                'item_type' => $item->item_type,
                'description' => $item->description,
                'inventory_item_id' => $item->inventory_item_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price ?? 0,
            ];
        }

        $this->selectedTemplate = null;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Template applied successfully.'
        ]);
    }

    public function getTemplatesProperty()
    {
        return ServiceTemplate::where('vendor_id', auth()->user()->vendor_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getInventoryPartsProperty()
    {
        return InventoryItem::where('vendor_id', auth()->user()->vendor_id)
            ->where('is_active', true)
            ->whereHas('category', fn($q) => $q->where('type', 'parts'))
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();
    }

    public function getSubtotalProperty()
    {
        return collect($this->items)->sum(fn($item) =>
            ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)
        );
    }

    // ═══════════════════════════════════════════════════════════════════════
    // STEP 4: SUBMIT
    // ═══════════════════════════════════════════════════════════════════════

    public function save()
    {
        // Final validation
        $this->validateStep1();
        $this->validateStep2();
        $this->validateStep3();

        // Check branch exists
        $branchId = session('current_branch_id');
        if (!$branchId) {
            session()->flash('error', 'Session expired. Please refresh the page.');
            return;
        }

        // Check closure time
        $vendor = auth()->user()->vendor;
        $settings = $vendor?->settings ?? [];

        if (!empty($settings['closure_enabled']) && !empty($settings['closure_time'])) {
            $now = now()->format('H:i');
            if ($now >= $settings['closure_time']) {
                session()->flash('error', 'New orders cannot be created after ' . $settings['closure_time'] . '. System is closed for the day.');
                return;
            }
        }

        try {
            DB::beginTransaction();

            // Create work order
            $workOrder = WorkOrder::create([
                'branch_id' => $branchId,
                'customer_id' => $this->customer_id,
                'vehicle_id' => $this->vehicle_id,
                'service_bay_id' => $this->service_bay_id,
                'assigned_technician_id' => $this->assigned_technician_id,
                'created_by' => auth()->id(),
                'type' => $this->type,
                'status' => 'open',
                'priority' => $this->priority,
                'is_combo' => $this->is_combo,
                'mileage_in' => $this->mileage_in,
                'customer_notes' => $this->customer_notes,
                'estimated_completion' => $this->estimated_completion,
                'checked_in_at' => now(),
            ]);

            Log::info('Work order created', [
                'id' => $workOrder->id,
                'order_number' => $workOrder->order_number,
            ]);

            // Create work order items
            foreach ($this->items as $item) {
                $workOrder->items()->create([
                    'item_type' => $item['item_type'],
                    'description' => $item['description'],
                    'inventory_item_id' => $item['inventory_item_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $this->isJobcarder() ? 0 : ($item['unit_price'] ?? 0),
                    'discount' => 0,
                    'total' => $this->isJobcarder() ? 0 : (($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)),
                ]);
            }

            // Mark service bay as occupied
            if ($workOrder->serviceBay) {
                $workOrder->serviceBay->markAsOccupied();
            }

            DB::commit();

            session()->flash('success', 'Work order #' . $workOrder->order_number . ' created successfully.');

            return redirect()->route('work-orders.show', $workOrder);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating work order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Error creating work order: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    public function isJobcarder(): bool
    {
        return auth()->user()->hasRole('jobcarder');
    }

    public function getSelectedCustomerProperty()
    {
        return $this->customer_id ? Customer::find($this->customer_id) : null;
    }

    public function getSelectedVehicleProperty()
    {
        return $this->vehicle_id ? Vehicle::find($this->vehicle_id) : null;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // RENDER
    // ═══════════════════════════════════════════════════════════════════════

    public function render()
    {
        return view('livewire.work-orders.create-work-order')
            ->layout('components.layouts.app', ['title' => 'New Work Order']);
    }
}
