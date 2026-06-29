<?php

namespace App\Domains\Operations\Livewire\Workshop;

use Livewire\Component;
use App\Domains\Operations\Models\WorkshopJobcard;
use App\Domains\Operations\Models\MechanicalWork;
use Illuminate\Support\Facades\DB;

class WorkshopJobcardShow extends Component
{
    public $workshopJobcardId;
    public $workshopJobcard;
    public $notes;
    public $mechanicalWorks = []; // keyed by id => ['repair_items','quantity','notes']

    public function mount($workshopJobcard)
    {
        $id = is_object($workshopJobcard) ? ($workshopJobcard->id ?? null) : $workshopJobcard;
        $this->workshopJobcardId = $id;
        $this->loadData();
    }

    protected function loadData()
    {
        $this->workshopJobcard = WorkshopJobcard::with(['mechanicalWorks.serviceType', 'jobcard'])->findOrFail($this->workshopJobcardId);
        $this->notes = $this->workshopJobcard->notes;
        $this->mechanicalWorks = [];
        foreach ($this->workshopJobcard->mechanicalWorks as $mw) {
            $this->mechanicalWorks[$mw->id] = [
                'service_type_id' => $mw->service_type_id,
                'repair_items' => $mw->repair_items,
                'quantity' => $mw->quantity,
                'notes' => $mw->notes,
            ];
        }
    }

    public function updateAll()
    {
        $this->validateAll();

        return DB::transaction(function () {
            $wj = WorkshopJobcard::findOrFail($this->workshopJobcardId);
            $wj->notes = $this->notes;
            $wj->save();

            foreach ($this->mechanicalWorks as $id => $data) {
                $mw = MechanicalWork::find($id);
                if (!$mw) continue;
                $mw->repair_items = $data['repair_items'] ?? null;
                $mw->quantity = $data['quantity'] ?? 1;
                $mw->notes = $data['notes'] ?? null;
                $mw->save();
            }

            session()->flash('success', 'Workshop jobcard updated.');
            $this->loadData();
        });
    }

    protected function validateAll()
    {
        foreach ($this->mechanicalWorks as $id => $data) {
            if (!isset($data['quantity']) || (int)$data['quantity'] < 1) {
                $this->addError('mechanicalWorks.'.$id.'.quantity', 'Quantity must be at least 1');
                throw \Illuminate\Validation\ValidationException::withMessages(['mechanicalWorks.'.$id.'.quantity' => 'Quantity must be at least 1']);
            }
        }
    }

    public function deleteMechanicalWork($id)
    {
        $mw = MechanicalWork::find($id);
        if ($mw) {
            $mw->delete();
            $this->loadData();
            session()->flash('success', 'Mechanical work deleted.');
        }
    }

    public function render()
    {
        return view('livewire.workshop.workshop-jobcard-show');
    }
}
