<div class="wizard-shell">
    @php
        $wizardSteps = [
            1 => ['title' => 'Customer & Vehicle', 'note' => 'Identify owner and car'],
            2 => ['title' => 'Service Details', 'note' => 'Scope and assignment'],
            3 => ['title' => 'Items', 'note' => 'Labor and parts plan'],
            4 => ['title' => 'Review', 'note' => 'Confirm and create order'],
        ];
        $progress = (int) round(($currentStep / $totalSteps) * 100);
    @endphp

    <div class="wizard-header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('work-orders.index') }}" class="btn btn-ghost btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Create Work Order</h1>
                    <p class="text-sm text-base-content/60">Structured 4-step intake for service, repair, and combo jobs.</p>
                </div>
            </div>
            <div class="badge badge-outline badge-lg">Step {{ $currentStep }} of {{ $totalSteps }}</div>
        </div>

        <div class="wizard-progress">
            <div class="wizard-progress-track">
                <div class="wizard-progress-fill" style="width: {{ $progress }}%;"></div>
            </div>
        </div>

        <div class="wizard-steps">
            @foreach($wizardSteps as $stepNumber => $meta)
                <button
                    type="button"
                    wire:click="goToStep({{ $stepNumber }})"
                    class="wizard-step {{ $currentStep === $stepNumber ? 'is-active' : '' }} {{ $currentStep > $stepNumber ? 'is-complete' : '' }}"
                    @if($stepNumber > $currentStep) disabled @endif
                >
                    <span class="wizard-step-index">{{ $stepNumber }}</span>
                    <span>
                        <span class="wizard-step-title">{{ $meta['title'] }}</span>
                        <span class="wizard-step-note">{{ $meta['note'] }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="wizard-step-content">
    {{-- Step Content --}}
    @if ($currentStep === 1)
        @include('livewire.work-orders.partials.step-customer-vehicle')
    @endif

    @if ($currentStep === 2)
        @include('livewire.work-orders.partials.step-job-details')
    @endif

    @if ($currentStep === 3)
        @include('livewire.work-orders.partials.step-items')
    @endif

    @if ($currentStep === 4)
        @include('livewire.work-orders.partials.step-review')
    @endif
    </div>

    {{-- Modals --}}
    @include('livewire.work-orders.partials.modal-quick-add-customer')
    @include('livewire.work-orders.partials.modal-quick-add-vehicle')
</div>

