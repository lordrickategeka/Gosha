<?php

namespace App\Domains\ServiceConfig\Livewire\QualityCheck;

use App\Domains\ServiceConfig\Models\QualityCheck;
use App\Domains\ServiceConfig\Models\QualityCheckTemplate;
use App\Domains\Operations\Models\WorkOrder;
use Livewire\Component;

class QualityCheckFormComponent extends Component
{
    public WorkOrder $workOrder;
    public QualityCheck $qualityCheck;

    public $checkItems = [];
    public $generalNotes = '';
    public $inspectionDate;
    public $requiresRoadTest = false;

    protected $rules = [
        'checkItems.*.status' => 'required|in:ok,needs_attention,n_a',
        'checkItems.*.remarks' => 'nullable|string|max:500',
        'generalNotes' => 'nullable|string|max:2000',
        'inspectionDate' => 'required|date',
    ];

    public function mount(WorkOrder $workOrder)
    {
        $this->authorize('quality-check.create');

        $this->workOrder = $workOrder->load(['vehicle', 'customer', 'qualityCheck', 'items']);

        // Get or create quality check
        $this->qualityCheck = $this->workOrder->qualityCheck ?? QualityCheck::create([
            'work_order_id' => $this->workOrder->id,
            'vehicle_id' => $this->workOrder->vehicle_id,
            'customer_id' => $this->workOrder->customer_id,
            'vendor_id' => $this->workOrder->vendor_id,
            'branch_id' => $this->workOrder->branch_id,
            'inspector_user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        $this->inspectionDate = $this->qualityCheck->inspection_date?->format('Y-m-d') ?? today()->format('Y-m-d');
        $this->generalNotes = $this->qualityCheck->general_notes ?? '';

        // Check if any service category requires road test
        $this->requiresRoadTest = $this->workOrder->items()
            ->where('description', 'like', '%road test%')
            ->exists();

        $this->loadChecklistItems();
    }

    public function loadChecklistItems()
    {
        $templates = QualityCheckTemplate::getForVendor($this->workOrder->vendor_id);

        // Load existing responses if any
        $existingItems = $this->qualityCheck->items()->get()->keyBy(function ($item) {
            return $item->section . '|' . $item->item_name;
        });

        foreach ($templates as $section => $items) {
            // Skip road test section if not required
            if ($section === 'road_test' && !$this->requiresRoadTest) {
                continue;
            }

            foreach ($items as $template) {
                $key = $section . '|' . $template->item_name;
                $existing = $existingItems->get($key);

                $this->checkItems[$key] = [
                    'section' => $section,
                    'item_name' => $template->item_name,
                    'status' => $existing?->status,
                    'remarks' => $existing?->remarks ?? '',
                ];
            }
        }
    }

    public function saveAsDraft()
    {
        $this->saveItems();

        session()->flash('success', 'Quality check saved as draft.');
    }

    public function submit()
    {
        $this->validate();

        // Check if all items have a status
        $incompleteItems = collect($this->checkItems)->filter(fn($item) => empty($item['status']))->count();

        if ($incompleteItems > 0) {
            session()->flash('error', "Please complete all checklist items. {$incompleteItems} items remaining.");
            return;
        }

        // Validate road test items if required
        if ($this->requiresRoadTest) {
            $roadTestItems = collect($this->checkItems)
                ->filter(fn($item) => $item['section'] === 'road_test')
                ->filter(fn($item) => empty($item['status']));

            if ($roadTestItems->count() > 0) {
                session()->flash('error', 'Road test is mandatory for this work order. Please complete all road test items.');
                return;
            }
        }

        $this->saveItems();

        // Update quality check
        $this->qualityCheck->update([
            'inspector_user_id' => auth()->id(),
            'inspection_date' => $this->inspectionDate,
            'general_notes' => $this->generalNotes,
        ]);

        // Mark as completed and determine status
        $this->qualityCheck->markAsCompleted();

        if ($this->qualityCheck->status === 'passed') {
            session()->flash('success', 'Quality check completed successfully. Work order is now ready for invoicing.');
        } else {
            session()->flash('warning', 'Quality check completed with issues. Please address the items marked as "Needs Attention" before proceeding.');
        }

        return redirect()->route('work-orders.show', $this->workOrder);
    }

    protected function saveItems()
    {
        // Delete existing items
        $this->qualityCheck->items()->delete();

        // Create new items
        foreach ($this->checkItems as $item) {
            if (!empty($item['status'])) {
                $this->qualityCheck->items()->create([
                    'section' => $item['section'],
                    'item_name' => $item['item_name'],
                    'status' => $item['status'],
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }
        }
    }

    public function getGroupedItemsProperty()
    {
        $grouped = collect($this->checkItems)->groupBy('section');

        $sections = QualityCheckTemplate::SECTIONS;

        // Remove road test if not required
        if (!$this->requiresRoadTest) {
            unset($sections['road_test']);
        }

        return $sections;
    }

    public function render()
    {
        return view('livewire.quality-check.quality-check-form-component')
            ->layout('components.layouts.app', ['title' => 'Quality Check - ' . $this->workOrder->order_number]);
    }
}
