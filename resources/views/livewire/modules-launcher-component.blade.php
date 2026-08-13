<div class="gh-page">
    <p class="gh-muted" style="font-size:13px; max-width:600px; margin:0;">
        Every part of the garage is its own module with its own dashboard. Open one to work inside it — the sidebar follows the module you are in.
    </p>

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
