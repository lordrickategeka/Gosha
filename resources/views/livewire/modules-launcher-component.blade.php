<div class="gh-page">
    <x-garage-setup />
    <p class="gh-muted" style="font-size:13px; max-width:600px; margin:0;">
        Every part of the garage is its own module with its own dashboard. Open one to work inside it — the sidebar follows the module you are in.
    </p>

    @if(auth()->user()?->hasRole('vendor-owner') && in_array(auth()->user()->vendor?->vendor_type, ['garage', 'car_wash', 'hybrid']))
        <section class="gh-card gh-card--pad" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
            <div><h2 class="gh-card__title">{{ auth()->user()->vendor->supplier_requested_at ? 'Your supplier module' : 'Sell parts through your garage' }}</h2><p class="gh-muted" style="margin-top:6px;font-size:13px;">{{ auth()->user()->vendor->supplier_requested_at ? 'Check approval, then open your supplier workspace using this same account.' : 'Add supplier sales to this business. Keep your existing garage account, records and subscription.' }}</p></div>
            <a href="{{ route('supplier.enable') }}" class="gh-btn gh-btn--primary">{{ auth()->user()->vendor->supplier_requested_at ? 'View supplier access' : 'Add supplier module' }}</a>
        </section>
    @endif
    <div class="gh-grid-4">
        @forelse ($this->modules as $module)
            @php
                $target = \App\Shared\Navigation\ModuleRegistry::visibleItems($module)[0]['route'] ?? null;
            @endphp
            @if ($target)
                <a href="{{ route($target) }}" class="gh-module-card">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                        <div class="gh-module-card__icon {{ isset($module['iconTone']) ? 'gh-module-card__icon--'.$module['iconTone'] : '' }}">
                            <x-icon :name="$module['icon']" class="gh-icon" />
                        </div>
                        @if ($module['stats']['badge'])
                            <span class="gh-badge gh-badge--{{ $module['stats']['badge']['tone'] }}">{{ $module['stats']['badge']['label'] }}</span>
                        @endif
                    </div>
                    <div>
                        <div class="gh-module-card__name">{{ $module['name'] }}</div>
                        <div class="gh-module-card__blurb">{{ $module['blurb'] }}</div>
                    </div>
                    @if (count($module['stats']['rows']))
                        <div class="gh-module-card__stats">
                            @foreach ($module['stats']['rows'] as $stat)
                                <div class="gh-module-card__stat">
                                    <span class="gh-muted">{{ $stat['label'] }}</span>
                                    <b>{{ $stat['value'] }}</b>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </a>
            @endif
        @empty
            <div class="gh-card gh-card--pad" style="grid-column:1/-1; text-align:center; color:var(--gh-ink-faint);">
                No modules are available for your account yet.
            </div>
        @endforelse
    </div>
</div>
