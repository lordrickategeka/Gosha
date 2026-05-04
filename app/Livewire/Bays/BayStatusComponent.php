<?php

namespace App\Livewire\Bays;

use App\Models\ServiceBay;
use App\Models\WashBay;
use Livewire\Component;

class BayStatusComponent extends Component
{
    // Service Bay form
    public $showServiceBayModal = false;
    public $serviceBayName = '';
    public $serviceBayType = 'general';
    public $serviceBayNotes = '';
    public $editingServiceBayId = null;

    // Wash Bay form
    public $showWashBayModal = false;
    public $washBayName = '';
    public $washBayType = 'standard';
    public $washBayNotes = '';
    public $editingWashBayId = null;

    // Delete confirmation
    public $confirmingDeleteId = null;
    public $confirmingDeleteType = null;

    public function createServiceBay()
    {
        $this->resetServiceBayForm();
        $this->editingServiceBayId = null;
        $this->showServiceBayModal = true;
    }

    public function editServiceBay(ServiceBay $bay)
    {
        $this->authorize('manage_bays');
        abort_unless(in_array($bay->branch_id, auth()->user()->getAccessibleBranchIds()), 403);
        $this->editingServiceBayId = $bay->id;
        $this->serviceBayName = $bay->name;
        $this->serviceBayType = $bay->bay_type;
        $this->serviceBayNotes = $bay->notes ?? '';
        $this->showServiceBayModal = true;
    }

    public function saveServiceBay()
    {
        $this->authorize('manage_bays');

        $this->validate([
            'serviceBayName' => 'required|string|max:100',
            'serviceBayType' => 'required|in:general,electrical,bodywork,diagnostics,ac,tyres',
            'serviceBayNotes' => 'nullable|string|max:500',
        ]);

        $data = [
            'name' => $this->serviceBayName,
            'bay_type' => $this->serviceBayType,
            'notes' => $this->serviceBayNotes ?: null,
        ];

        if ($this->editingServiceBayId) {
            $serviceBay = ServiceBay::findOrFail($this->editingServiceBayId);
            abort_unless(in_array($serviceBay->branch_id, auth()->user()->getAccessibleBranchIds()), 403);
            $serviceBay->update($data);
            session()->flash('success', 'Service bay updated.');
        } else {
            $data['branch_id'] = session('current_branch_id');
            $data['status'] = 'available';
            ServiceBay::create($data);
            session()->flash('success', 'Service bay created.');
        }

        $this->showServiceBayModal = false;
        $this->resetServiceBayForm();
    }

    public function createWashBay()
    {
        $this->resetWashBayForm();
        $this->editingWashBayId = null;
        $this->showWashBayModal = true;
    }

    public function editWashBay(WashBay $bay)
    {
        $this->authorize('manage_bays');
        abort_unless(in_array($bay->branch_id, auth()->user()->getAccessibleBranchIds()), 403);
        $this->editingWashBayId = $bay->id;
        $this->washBayName = $bay->name;
        $this->washBayType = $bay->bay_type instanceof \App\Enums\WashBayType ? $bay->bay_type->value : $bay->bay_type;
        $this->washBayNotes = $bay->notes ?? '';
        $this->showWashBayModal = true;
    }

    public function saveWashBay()
    {
        $this->authorize('manage_bays');

        $this->validate([
            'washBayName'  => 'required|string|max:100',
            'washBayType'  => 'required|in:basic,standard,premium,full_service,detailing,automated',
            'washBayNotes' => 'nullable|string|max:500',
        ]);

        $data = [
            'name'     => $this->washBayName,
            'bay_type' => $this->washBayType,
            'notes'    => $this->washBayNotes ?: null,
        ];

        if ($this->editingWashBayId) {
            $washBay = WashBay::findOrFail($this->editingWashBayId);
            abort_unless(in_array($washBay->branch_id, auth()->user()->getAccessibleBranchIds()), 403);
            $washBay->update($data);
            session()->flash('success', 'Wash bay updated.');
        } else {
            $data['branch_id'] = session('current_branch_id');
            $data['status'] = 'available';
            WashBay::create($data);
            session()->flash('success', 'Wash bay created.');
        }

        $this->showWashBayModal = false;
        $this->resetWashBayForm();
    }

    public function confirmDelete($id, $type)
    {
        $this->confirmingDeleteId = $id;
        $this->confirmingDeleteType = $type;
    }

    public function deleteBay()
    {
        $this->authorize('manage_bays');

        if ($this->confirmingDeleteType === 'service') {
            $bay = ServiceBay::findOrFail($this->confirmingDeleteId);
            abort_unless(in_array($bay->branch_id, auth()->user()->getAccessibleBranchIds()), 403);
            if ($bay->isOccupied()) {
                session()->flash('error', 'Cannot delete an occupied bay.');
            } else {
                $bay->delete();
                session()->flash('success', "{$bay->name} deleted.");
            }
        } elseif ($this->confirmingDeleteType === 'wash') {
            $bay = WashBay::findOrFail($this->confirmingDeleteId);
            abort_unless(in_array($bay->branch_id, auth()->user()->getAccessibleBranchIds()), 403);
            if ($bay->isOccupied()) {
                session()->flash('error', 'Cannot delete an occupied bay.');
            } else {
                $bay->delete();
                session()->flash('success', "{$bay->name} deleted.");
            }
        }

        $this->confirmingDeleteId = null;
        $this->confirmingDeleteType = null;
    }

    public function cancelDelete()
    {
        $this->confirmingDeleteId = null;
        $this->confirmingDeleteType = null;
    }

    public function markServiceBayAvailable(ServiceBay $bay)
    {
        $this->authorize('manage_bays');
        abort_unless(in_array($bay->branch_id, auth()->user()->getAccessibleBranchIds()), 403);
        $bay->markAsAvailable();
        session()->flash('success', "{$bay->name} marked as available.");
    }

    public function markServiceBayMaintenance(ServiceBay $bay)
    {
        $this->authorize('manage_bays');
        abort_unless(in_array($bay->branch_id, auth()->user()->getAccessibleBranchIds()), 403);
        $bay->update(['status' => 'maintenance']);
        session()->flash('success', "{$bay->name} set to maintenance.");
    }

    public function markWashBayAvailable(WashBay $bay)
    {
        $this->authorize('manage_bays');
        abort_unless(in_array($bay->branch_id, auth()->user()->getAccessibleBranchIds()), 403);
        $bay->markAsAvailable();
        session()->flash('success', "{$bay->name} marked as available.");
    }

    public function markWashBayMaintenance(WashBay $bay)
    {
        $this->authorize('manage_bays');
        abort_unless(in_array($bay->branch_id, auth()->user()->getAccessibleBranchIds()), 403);
        $bay->markAsMaintenance();
        session()->flash('success', "{$bay->name} set to maintenance.");
    }

    public function getServiceBaysProperty()
    {
        return ServiceBay::whereIn('branch_id', auth()->user()->getAccessibleBranchIds())
            ->when(session('current_branch_id'), fn($q) => $q->where('branch_id', session('current_branch_id')))
            ->with(['currentWorkOrder.vehicle', 'currentWorkOrder.assignedTechnician'])
            ->get();
    }

    public function getWashBaysProperty()
    {
        return WashBay::whereIn('branch_id', auth()->user()->getAccessibleBranchIds())
            ->when(session('current_branch_id'), fn($q) => $q->where('branch_id', session('current_branch_id')))
            ->with(['currentWashOrder.vehicle', 'currentWashOrder.assignedAttendant'])
            ->get();
    }

    private function resetServiceBayForm()
    {
        $this->serviceBayName = '';
        $this->serviceBayType = 'general';
        $this->serviceBayNotes = '';
        $this->editingServiceBayId = null;
    }

    private function resetWashBayForm()
    {
        $this->washBayName = '';
        $this->washBayType = 'standard';
        $this->washBayNotes = '';
        $this->editingWashBayId = null;
    }

    public function render()
    {
        return view('livewire.bays.bay-status-component')
            ->layout('components.layouts.app', ['title' => 'Bay Status']);
    }
}
