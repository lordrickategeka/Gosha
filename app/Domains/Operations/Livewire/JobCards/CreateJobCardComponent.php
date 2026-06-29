<?php
namespace App\Domains\Operations\Livewire\JobCards;

use Livewire\Component;
use App\Domains\ServiceConfig\Models\ServiceType;
use App\Domains\Vehicles\Models\VehicleType;

use App\Domains\Operations\Services\JobCardService;
use App\Domains\Vehicles\Models\VehicleItem;
use Illuminate\Support\Facades\Auth;
use App\Domains\Operations\Models\JobCard;
use App\Domains\Vehicles\Models\Vehicle;

class CreateJobCardComponent extends Component
{
    // Items left on vehicle
    public $vehicle_items = [];
    public $item_name = '';
    public $item_description = '';
    public $item_quantity = 1;
    public $item_part_number = '';
    public $addingNewVehicle = false;

    public function addVehicleItem()
    {
        if (trim($this->item_name) !== '' && $this->item_quantity > 0) {
            // Check if the item already exists in the list
            foreach ($this->vehicle_items as $item) {
                if ($item['item_name'] === $this->item_name && $item['part_number'] === $this->item_part_number) {
                    session()->flash('error', 'This item is already in the list.');
                    return;
                }
            }

            // Add the new item to the list
            $this->vehicle_items[] = [
                'item_name' => $this->item_name,
                'description' => $this->item_description,
                'quantity' => $this->item_quantity,
                'part_number' => $this->item_part_number,
                'vehicle_id' => $this->vehicle_id,
                'customer_id' => $this->customer_id,
            ];

            // Reset the form fields after clicking Add
            $this->reset(['item_name', 'item_description', 'item_quantity', 'item_part_number']);
        } else {
            session()->flash('error', 'Note Item has already been added.');
        }
    }

    public function removeVehicleItem($index)
    {
        if (isset($this->vehicle_items[$index])) {
            array_splice($this->vehicle_items, $index, 1);
        }
    }
    public $step = 1;
    public $editingJobCardId = null;

    public function nextStep()
    {
        $this->step++;
    }

    public function prevStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }
    public $customer_id = '';
    public $phone = '';
    public $customer_name = '';
    public $company_name = '';
    public $email = '';
    public $contact_person = '';
    public $address = '';

    public $vehicle_id = '';
    public $vehicle_type_id = '';
    public $vehicle_name = '';
    public $number_plate = '';
    public $chasis_number = '';
    public $color = '';
    public $mileage = null;
    public $fuel_type = '';
    public $fuel_level = '';
    public $physical_condition = '';
    public $vin_number = '';

    public $service_types = [];
    public $notes = '';
    public $intake_datetime = '';
    // Client narrations
    public $client_narrations = [];
    public $narration_input = '';

    public $customerSuggestions = [];
    public $vehicleSuggestions = [];
    public $showCustomerSuggestions = false;
    public $showVehicleSuggestions = false;

    public $totalSteps = 3;

    public function getProgressPercentageProperty()
    {
        return ($this->step / $this->totalSteps) * 100;
    }

    public function render()
    {
        // You should pass $serviceTypes and $vehicleTypes from here
        $serviceTypes = \App\Domains\ServiceConfig\Models\ServiceType::all();
        $vehicleTypes = \App\Domains\Vehicles\Models\VehicleType::all();
        return view('livewire.job-cards.create-job-card', [
            'serviceTypes' => $serviceTypes,
            'vehicleTypes' => $vehicleTypes,
        ]);
    }

    public function mount($jobCard = null)
    {
        $jobCardId = null;
        if ($jobCard) {
            $jobCardId = is_object($jobCard) ? ($jobCard->id ?? null) : $jobCard;
        }
        if ($jobCardId) {
            $jobCard = \App\Domains\Operations\Models\JobCard::with(['customer', 'vehicle', 'clientNarrations'])->find($jobCardId);
            if ($jobCard) {
                $this->editingJobCardId = $jobCard->id;
                // populate customer fields
                $this->customer_id = $jobCard->customer_id;
                $this->customer_name = $jobCard->customer->customer_name ?? $this->customer_name;
                $this->phone = $jobCard->customer->phone ?? $this->phone;
                $this->email = $jobCard->customer->email ?? $this->email;
                $this->company_name = $jobCard->customer->company_name ?? $this->company_name;
                $this->contact_person = $jobCard->customer->contact_person ?? $this->contact_person;
                $this->address = $jobCard->customer->address ?? $this->address;

                // populate vehicle fields
                $this->vehicle_id = $jobCard->vehicle_id;
                if ($jobCard->vehicle) {
                    $this->vehicle_name = $jobCard->vehicle->vehicle_name;
                    $this->number_plate = $jobCard->vehicle->number_plate;
                    $this->chasis_number = $jobCard->vehicle->chasis_number;
                    $this->color = $jobCard->vehicle->color;
                    $this->vehicle_type_id = $jobCard->vehicle->vehicle_type_id;
                    $this->mileage = $jobCard->vehicle->mileage ?? null;
                    $this->fuel_type = $jobCard->vehicle->fuel_type ?? '';
                    $this->fuel_level = $jobCard->vehicle->fuel_level ?? '';
                    $this->physical_condition = $jobCard->vehicle->physical_condition ?? '';
                    $this->vin_number = $jobCard->vehicle->vin_number ?? '';
                }

                // notes and narrations
                $this->notes = $jobCard->notes ?? '';
                $this->client_narrations = $jobCard->clientNarrations->map(function($n){ return ['issue' => $n->issue]; })->toArray();
            }
        }
    }

    public function searchCustomers()
    {
        if (strlen($this->customer_name) < 2) {
            $this->customerSuggestions = [];
            $this->showCustomerSuggestions = false;
            return;
        }
        $this->customerSuggestions = \App\Domains\CRM\Models\Customer::where('customer_name', 'like', "%{$this->customer_name}%")
            ->orWhere('phone', 'like', "%{$this->customer_name}%")
            ->limit(10)
            ->get()
            ->toArray();
        $this->showCustomerSuggestions = true;
    }

    public function selectCustomer($customerId)
    {
        $customer = \App\Domains\CRM\Models\Customer::with('vehicles')->find($customerId);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customer_name = $customer->customer_name;
            $this->phone = $customer->phone;
            $this->email = $customer->email;
            $this->company_name = $customer->company_name;
            $this->contact_person = $customer->contact_person;
            $this->address = $customer->address;
            // Preload vehicles for dropdown
            $this->vehicleSuggestions = $customer->vehicles->map(function($v) {
                return [
                    'id' => $v->id,
                    'vehicle_name' => $v->vehicle_name,
                    'number_plate' => $v->number_plate,
                    'customer_name' => $this->customer_name,
                ];
            })->toArray();
            // Add a 'New Vehicle' option
            $this->vehicleSuggestions[] = [
                'id' => 'new',
                'vehicle_name' => 'Add New Vehicle',
                'number_plate' => '',
                'customer_name' => $this->customer_name,
            ];
        }
        $this->showCustomerSuggestions = false;
        $this->customerSuggestions = [];
    }

    public function searchVehicles()
    {
        if (strlen($this->vehicle_name) < 2) {
            $this->vehicleSuggestions = [];
            $this->showVehicleSuggestions = false;
            return;
        }
        $query = \App\Domains\Vehicles\Models\Vehicle::query();
        $query->where('vehicle_name', 'like', "%{$this->vehicle_name}%")
            ->orWhere('number_plate', 'like', "%{$this->vehicle_name}%");
        if ($this->customer_id) {
            $query->where('customer_id', $this->customer_id);
        }
        $this->vehicleSuggestions = $query->limit(10)->get()->toArray();
        $this->showVehicleSuggestions = true;
    }

    public function selectVehicle($vehicleId)
    {
        if ($vehicleId === 'new') {
            $this->addingNewVehicle = true;
            $this->vehicle_id = '';
            $this->vehicle_name = '';
            $this->number_plate = '';
            $this->chasis_number = '';
            $this->color = '';
            $this->vehicle_type_id = '';
        } else {
            $vehicle = \App\Domains\Vehicles\Models\Vehicle::find($vehicleId);
            if ($vehicle) {
                $this->vehicle_id = $vehicle->id;
                $this->vehicle_name = $vehicle->vehicle_name;
                $this->number_plate = $vehicle->number_plate;
                $this->chasis_number = $vehicle->chasis_number;
                $this->color = $vehicle->color;
                $this->vehicle_type_id = $vehicle->vehicle_type_id;
                $this->mileage = $vehicle->mileage ?? null;
                $this->fuel_type = $vehicle->fuel_type ?? '';
                $this->fuel_level = $vehicle->fuel_level ?? '';
                $this->physical_condition = $vehicle->physical_condition ?? '';
                $this->vin_number = $vehicle->vin_number ?? '';
                $this->addingNewVehicle = false;
            }
        }
        $this->showVehicleSuggestions = false;
        $this->vehicleSuggestions = [];
    }

    public function resetForm()
    {
        $this->reset([
            'customer_id', 'phone', 'customer_name', 'company_name', 'email', 'contact_person', 'address',
            'vehicle_id', 'vehicle_type_id', 'vehicle_name', 'number_plate', 'chasis_number', 'color',
            'service_types', 'notes', 'intake_datetime',
            'customerSuggestions', 'vehicleSuggestions', 'showCustomerSuggestions', 'showVehicleSuggestions',
            'vehicle_items', 'item_name', 'item_description', 'item_quantity', 'item_part_number',
            'mileage', 'fuel_type', 'fuel_level', 'physical_condition', 'vin_number',
            'client_narrations', 'narration_input',
        ]);
    }

    public function addClientNarration()
    {
        $text = trim($this->narration_input);
        if ($text === '') {
            return;
        }
        $this->client_narrations[] = [
            'issue' => $text,
        ];
        $this->narration_input = '';
    }

    public function removeClientNarration($index)
    {
        if (isset($this->client_narrations[$index])) {
            array_splice($this->client_narrations, $index, 1);
        }
    }

    public $formError = null;

    public function submit()
    {
        $this->formError = null;
        if ($this->step < 3) {
            $this->nextStep();
            return;
        }
        try {
            $this->validate([
                'customer_name' => 'required',
                'phone' => 'required',
                'vehicle_name' => 'required',
                'number_plate' => 'required',
                'vehicle_type_id' => 'required',
            ]);

            $service = new JobCardService();
            $data = [
                'customer_id' => $this->customer_id,
                'phone' => $this->phone,
                'customer_name' => $this->customer_name,
                'company_name' => $this->company_name,
                'email' => $this->email,
                'contact_person' => $this->contact_person,
                'address' => $this->address,

                // this have to be captued on the vehicles table
                'vehicle_id' => $this->vehicle_id,
                'vehicle_type_id' => $this->vehicle_type_id,
                'vehicle_name' => $this->vehicle_name,
                'number_plate' => $this->number_plate,
                'chasis_number' => $this->chasis_number,
                'color' => $this->color,
                'mileage' => $this->mileage,
                'fuel_type' => $this->fuel_type,
                'fuel_level' => $this->fuel_level,
                'physical_condition' => $this->physical_condition,
                'vin_number' => $this->vin_number,
                'service_types' => $this->service_types,
                'notes' => $this->notes,

                'intake_datetime' => $this->intake_datetime,
                'client_narrations' => $this->client_narrations,
                'staff_id' => Auth::id() ?? 1, // fallback for demo
            ];
            if ($this->editingJobCardId) {
                $jobCard = $service->updateJobCard($this->editingJobCardId, $data);
                session()->flash('message', 'Job card updated successfully!');
            } else {
                $jobCard = $service->createJobCard($data);
                session()->flash('message', 'Job card created successfully!');
            }

            // Save vehicle items if any, using the actual vehicle and customer IDs from the created job card
            if (!empty($this->vehicle_items) && $jobCard->vehicle_id && $jobCard->customer_id) {
                foreach ($this->vehicle_items as $item) {
                    VehicleItem::create([
                        'vehicle_id' => $jobCard->vehicle_id,
                        'customer_id' => $jobCard->customer_id,
                        'job_card_id' => $jobCard->id,
                        'item_name' => $item['item_name'],
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'part_number' => $item['part_number'],
                    ]);
                }
            }

            return redirect()->route('job-cards.index');
        } catch (\Throwable $e) {
            $this->formError = $e->getMessage();
        }
    }

    public function saveJobCard()
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_name' => 'required|string|max:255',
            'number_plate' => 'required|string|max:255',
            'service_types' => 'required|array',
            'intake_datetime' => 'required|date',
        ]);

        // Handle new vehicle creation
        if ($this->addingNewVehicle) {
            $vehicle = Vehicle::create([
                'customer_id' => $this->customer_id,
                'vehicle_name' => $this->vehicle_name,
                'number_plate' => $this->number_plate,
                'chasis_number' => $this->chasis_number,
                'color' => $this->color,
                'vehicle_type_id' => $this->vehicle_type_id,
                'mileage' => $this->mileage,
                'fuel_type' => $this->fuel_type,
                'fuel_level' => $this->fuel_level,
                'physical_condition' => $this->physical_condition,
                'vin_number' => $this->vin_number,
            ]);

            $this->vehicle_id = $vehicle->id;
        }

        // Create or update the job card
        $jobCardData = [
            'customer_id' => $this->customer_id,
            'vehicle_id' => $this->vehicle_id,
            'service_types' => json_encode($this->service_types),
            'notes' => $this->notes,
            'intake_datetime' => $this->intake_datetime,
        ];

        if ($this->editingJobCardId) {
            $jobCard = JobCard::findOrFail($this->editingJobCardId);
            $jobCard->update($jobCardData);
            session()->flash('message', 'Job card updated successfully.');
        } else {
            JobCard::create($jobCardData);
            session()->flash('message', 'Job card created successfully.');
        }

        $this->resetForm();
        return redirect()->route('job-cards.index');
    }
}
