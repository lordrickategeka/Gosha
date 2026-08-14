<div class="gh-page">
    @php
        $wizardSteps = [
            1 => ['title' => 'Customer & Vehicle', 'note' => 'Identify owner and car'],
            2 => ['title' => 'Service Details', 'note' => 'Scope and assignment'],
            3 => ['title' => 'Items', 'note' => 'Labor and parts plan'],
            4 => ['title' => 'Review', 'note' => 'Confirm and create order'],
        ];
        $progress = (int) round(($currentStep / $totalSteps) * 100);
    @endphp

    <div style="display:flex; flex-direction:column; gap:16px;">
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <a href="{{ route('work-orders.index') }}" class="gh-btn gh-btn--sm">←</a>
                <div>
                    <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Create Work Order</div>
                    <p class="gh-muted" style="font-size:12.5px; margin:4px 0 0;">Structured 4-step intake for service, repair, and combo jobs.</p>
                </div>
            </div>
            <span class="gh-badge">Step {{ $currentStep }} of {{ $totalSteps }}</span>
        </div>

        <div class="gh-meter"><div class="gh-meter__fill" style="width: {{ $progress }}%;"></div></div>

        <div class="gh-steps" style="flex-wrap:wrap;">
            @foreach($wizardSteps as $stepNumber => $meta)
                <button
                    type="button"
                    wire:click="goToStep({{ $stepNumber }})"
                    class="gh-steps__item {{ $currentStep === $stepNumber ? 'is-active' : '' }} {{ $currentStep > $stepNumber ? 'is-done' : '' }}"
                    style="border:0; background:none; cursor:pointer; padding:0;"
                    @if($stepNumber > $currentStep) disabled @endif
                >
                    <span class="gh-steps__num">{{ $stepNumber }}</span>
                    <span style="text-align:left;">
                        <span style="display:block; font-size:12.5px; font-weight:700;">{{ $meta['title'] }}</span>
                        <span class="gh-muted" style="font-size:11px;">{{ $meta['note'] }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <div>
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

    @include('livewire.work-orders.partials.modal-quick-add-customer')
    @include('livewire.work-orders.partials.modal-quick-add-vehicle')
</div>
