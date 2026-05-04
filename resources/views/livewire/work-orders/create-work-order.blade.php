<div>
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('work-orders.index') }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">New Work Order</h1>
            <p class="text-base-content/60">Create a new service or repair job</p>
        </div>
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

    {{-- Flash Messages --}}
    @if (session('error'))
        <div class="alert alert-error mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

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

    {{-- Modals --}}
    @include('livewire.work-orders.partials.modal-quick-add-customer')
    @include('livewire.work-orders.partials.modal-quick-add-vehicle')
</div>

@script
<script>
    // Listen for notify events
    $wire.on('notify', (event) => {
        const data = event[0];
        // You can integrate with your notification system here
        console.log(data.type, data.message);
    });
</script>
@endscript
