<?php

namespace App\Domains\Operations\Livewire\Workshop;

use Livewire\Component;
use App\Domains\Operations\Models\JobCard;
use App\Domains\Operations\Models\WorkshopJobcard;
use App\Domains\ServiceConfig\Models\ServiceType;
use App\Domains\Operations\Models\MechanicalWork;
use App\Domains\Vehicles\Models\VehicleItem;
use Illuminate\Support\Facades\DB;

class WorkshopJobcardCreate extends Component
{
    public $jobCardId;
    public $jobCard;
    public $notes;
    public $serviceTypes = [];
    // mechanicalWorks grouped by service type id: [ serviceId => [ ['repair_items','quantity','notes'], ... ] ]
    public $mechanicalWorks = [];
    public $selectedServiceType = null;
    public $searchResults = []; // [ serviceId => [ index => [results] ] ]



    public function mount($jobCard = null)
    {
        $id = is_object($jobCard) ? ($jobCard->id ?? null) : $jobCard;
        $this->jobCardId = $id;
        if ($this->jobCardId) {
            $this->jobCard = JobCard::find($this->jobCardId);
        }
        $this->serviceTypes = ServiceType::orderBy('name')->get();
    }

    public function save()
    {
        // Validate there is at least one mechanical work item across groups
        $hasItem = false;
        foreach ($this->mechanicalWorks as $sid => $items) {
            if (is_array($items) && count($items) > 0) {
                $hasItem = true;
                break;
            }
        }

        if (!$hasItem) {
            $this->addError('mechanicalWorks', 'Please add at least one mechanical work item.');
            return;
        }

        // Basic validation for each item
        foreach ($this->mechanicalWorks as $sid => $items) {
            $serviceExists = ServiceType::where('id', $sid)->exists();
            if (!$serviceExists) {
                $this->addError('mechanicalWorks', 'Invalid service type selected.');
                return;
            }

            foreach ($items as $i => $item) {
                if (empty($item['repair_items'])) {
                    $this->addError('mechanicalWorks.'.$sid.'.'.$i.'.repair_items', 'Please specify repair items.');
                    return;
                }

                if (!isset($item['quantity']) || !is_numeric($item['quantity']) || (int)$item['quantity'] < 1) {
                    $this->addError('mechanicalWorks.'.$sid.'.'.$i.'.quantity', 'Quantity must be at least 1.');
                    return;
                }
            }
        }

        return DB::transaction(function () {
            // provide explicit fields that may be non-nullable in the DB to avoid SQL errors
            $data = [
                'jobcard_id' => $this->jobCardId,
                'material_id' => null,
                'material_name' => null,
                'quantity' => 1,
                'notes' => $this->notes ?? null,
                'workshop_jobcard_number' => null, // Auto-generated in the model
            ];

            $wj = WorkshopJobcard::create($data);

            foreach ($this->mechanicalWorks as $sid => $items) {
                foreach ($items as $item) {
                    MechanicalWork::create([
                        'workshop_jobcard_id' => $wj->id,
                        'service_type_id' => (int) $sid,
                        'repair_items' => $item['repair_items'] ?? null,
                        'quantity' => $item['quantity'] ?? 1,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            session()->flash('success', 'Workshop jobcard and mechanical works created.');

            // Redirect to the workshop jobcard detail/edit page
            return redirect()->route('workshop-jobcards.show', $wj->id);
        });
    }

    public function addMechanicalWork($serviceTypeId = null)
    {
        $serviceTypeId = $serviceTypeId ?? $this->selectedServiceType;
        if (!$serviceTypeId) {
            return;
        }

        $sid = (string) $serviceTypeId;

        if (!isset($this->mechanicalWorks[$sid]) || !is_array($this->mechanicalWorks[$sid])) {
            $this->mechanicalWorks[$sid] = [];
        }

        $this->mechanicalWorks[$sid][] = [
            'repair_items' => '',
            'quantity' => 1,
            'notes' => '',
        ];
    }

    public function addSelectedServiceType()
    {
        $this->addMechanicalWork($this->selectedServiceType);
    }

    public function addItemToGroup($serviceTypeId)
    {
        $this->addMechanicalWork($serviceTypeId);
    }

    public function removeMechanicalWork($serviceTypeId, $index = null)
    {
        $sid = (string) $serviceTypeId;
        if (!isset($this->mechanicalWorks[$sid])) {
            return;
        }

        if ($index === null) {
            // remove entire group
            unset($this->mechanicalWorks[$sid]);
            return;
        }

        if (isset($this->mechanicalWorks[$sid][$index])) {
            array_splice($this->mechanicalWorks[$sid], $index, 1);
        }

        if (empty($this->mechanicalWorks[$sid])) {
            unset($this->mechanicalWorks[$sid]);
        }
    }

    // Search repair items (inventory) for an item input
    public function searchRepairItems($serviceTypeId, $itemIndex)
    {
        $sid = (string) $serviceTypeId;
        $query = $this->mechanicalWorks[$sid][$itemIndex]['repair_items'] ?? '';
        if (strlen($query) < 2) {
            $this->searchResults[$sid][$itemIndex] = [];
            return;
        }

        $results = VehicleItem::where('item_name', 'like', "%{$query}%")
            ->select('item_name')
            ->groupBy('item_name')
            ->limit(8)
            ->pluck('item_name')
            ->toArray();

        $this->searchResults[$sid][$itemIndex] = $results;
    }

    public function chooseRepairItem($serviceTypeId, $itemIndex, $name)
    {
        $sid = (string) $serviceTypeId;
        $this->mechanicalWorks[$sid][$itemIndex]['repair_items'] = $name;
        // clear suggestions
        $this->searchResults[$sid][$itemIndex] = [];
    }

    public function render()
    {
        return view('livewire.workshop.workshop-jobcard-create');
    }
}
