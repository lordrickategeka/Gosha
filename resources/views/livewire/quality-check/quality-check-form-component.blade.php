<div class="container mx-auto px-4 py-6">
    {{-- Header Section --}}
    <div class="bg-base-200 rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold">Vehicle Quality Check</h1>
                <p class="text-sm text-base-content/70">Work Order: {{ $workOrder->order_number }}</p>
            </div>
            <div class="badge badge-lg {{ $qualityCheck->status === 'passed' ? 'badge-success' : ($qualityCheck->status === 'has_issues' ? 'badge-warning' : 'badge-info') }}">
                {{ strtoupper(str_replace('_', ' ', $qualityCheck->status)) }}
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="font-semibold">Customer:</span> {{ $workOrder->customer->name }}
            </div>
            <div>
                <span class="font-semibold">Vehicle:</span> {{ $workOrder->vehicle->model }} ({{ $workOrder->vehicle->registration_number }})
            </div>
            <div>
                <span class="font-semibold">Mileage:</span> {{ number_format($workOrder->vehicle->current_mileage) }} km
            </div>
            <div>
                <span class="font-semibold">VIN:</span> {{ $workOrder->vehicle->vin_number ?? 'N/A' }}
            </div>
            <div>
                <span class="font-semibold">Inspection Date:</span>
                <input type="date" wire:model="inspectionDate" class="input input-sm input-bordered">
            </div>
            <div>
                <span class="font-semibold">Inspector:</span> {{ auth()->user()->name }}
            </div>
        </div>
    </div>

    {{-- Checklist Sections --}}
    <form wire:submit.prevent="submit">
        @include('livewire.quality-check.partials._checklist-items')

        {{-- General Notes Section --}}
        <div class="bg-base-100 rounded-lg shadow mb-6 p-6">
            <h3 class="text-lg font-bold mb-4">F. General Notes / Observations</h3>
            <textarea wire:model="generalNotes"
                      rows="5"
                      placeholder="Add any general observations, recommendations, or notes about the vehicle condition..."
                      class="textarea textarea-bordered w-full"></textarea>
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-4 justify-end">
            <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-ghost">
                Cancel
            </a>
            <button type="button" wire:click="saveAsDraft" class="btn btn-outline">
                Save Draft
            </button>
            <button type="submit" class="btn btn-primary">
                Submit Quality Check
            </button>
        </div>
    </form>
</div>
