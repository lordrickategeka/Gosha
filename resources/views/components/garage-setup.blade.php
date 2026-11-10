@php
    $setupUser = auth()->user();
    $missingMasterData = $setupUser?->isVendorOwner() && !$setupUser->is_platform_user ? app(\App\Support\MasterData::class)->missing($setupUser) : [];
@endphp
@if(count($missingMasterData))
    <section class="rounded-xl border border-base-300 bg-base-100 p-5 mb-5" aria-label="Garage setup checklist">
        <h2 class="font-semibold text-lg">Start here: {{ count($missingMasterData) }} master data {{ count($missingMasterData) === 1 ? 'item needs' : 'items need' }} attention</h2>
        <p class="text-sm text-base-content/60 mt-1">These recommended setup items are still missing. This list updates from your saved records and stays available after you sign in again.</p>
        <ul class="grid sm:grid-cols-2 gap-3 mt-4 text-sm">
            @foreach($missingMasterData as $setupItem)
                <li>
                    @if($setupItem['canOpen'])
                        <a href="{{ $setupItem['url'] }}" class="link link-primary">Set up {{ strtolower($setupItem['label']) }}</a>
                    @else
                        {{ $setupItem['label'] }} — ask your administrator
                    @endif
                    <p class="text-xs text-base-content/60 mt-1">{{ $setupItem['example'] }}</p>
                </li>
            @endforeach
        </ul>
        <a href="{{ route('master-data.index') }}" class="link link-primary inline-block mt-4 text-sm">Review all master data, including optional setup</a>
    </section>
@endif
