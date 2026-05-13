<div>
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Edit Work Order #{{ $workOrder->order_number }}</h1>
            <p class="text-base-content/60">Update service or repair job details</p>
        </div>
    </div>

    {{-- Status Badge --}}
    <div class="mb-4">
        <span class="badge badge-{{ $workOrder->statusColor }} badge-lg">
            {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
        </span>
    </div>

    {{-- Progress Indicator --}}
    <div class="mb-8">
        <ul class="steps steps-horizontal w-full">
            <li class="step {{ $currentStep >= 1 ? 'step-primary' : '' }}">
                <span class="hidden sm:inline">Customer & Vehicle</span>
                <span class="sm:hidden">Step 1</span>
            </li>
            <li class="step {{ $currentStep >= 2 ? 'step-primary' : '' }}">
                <span class="hidden sm:inline">Job Details</span>
                <span class="sm:hidden">Step 2</span>
            </li>
            <li class="step {{ $currentStep >= 3 ? 'step-primary' : '' }}">
                <span class="hidden sm:inline">Items</span>
                <span class="sm:hidden">Step 3</span>
            </li>
            <li class="step {{ $currentStep >= 4 ? 'step-primary' : '' }}">
                <span class="hidden sm:inline">Review</span>
                <span class="sm:hidden">Step 4</span>
            </li>
        </ul>
    </div>

    {{-- Step Content (reusing the same partials as create) --}}
    @if ($currentStep === 1)
        @include('livewire.work-orders.partials.step-customer-vehicle')
    @endif

    @if ($currentStep === 2)
        @include('livewire.work-orders.partials.step-job-details-edit')
    @endif

    @if ($currentStep === 3)
        @include('livewire.work-orders.partials.step-items')
    @endif

    @if ($currentStep === 4)
        @include('livewire.work-orders.partials.step-review-edit')
    @endif

    {{-- Modals --}}
    @include('livewire.work-orders.partials.modal-quick-add-customer')
    @include('livewire.work-orders.partials.modal-quick-add-vehicle')
</div>

