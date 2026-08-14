{{-- Quality Checklist Items Partial --}}
{{-- Displays grouped quality check items organized by section with radio status selectors --}}

@foreach ($this->groupedItems as $sectionKey => $sectionName)
    <div class="gh-card" style="overflow:hidden; margin-bottom:20px;">
        <div style="background:var(--gh-primary); color:var(--gh-primary-content); padding:14px 18px; display:flex; align-items:center; gap:8px;">
            <h2 style="font-size:14.5px; font-weight:700;">{{ $sectionName }}</h2>
            @if($sectionKey === 'road_test' && $requiresRoadTest)
                <span class="gh-badge" style="background:rgba(255,255,255,.25); color:#fff;">Mandatory</span>
            @endif
        </div>

        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th style="width:40%;">Item</th>
                        <th style="width:16.6%; text-align:center;">OK</th>
                        <th style="width:16.6%; text-align:center;">Needs Attention</th>
                        <th style="width:16.6%; text-align:center;">N/A</th>
                        <th style="width:33%;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (collect($checkItems)->where('section', $sectionKey) as $key => $item)
                        <tr>
                            <td style="font-weight:600;">{{ $item['item_name'] }}</td>
                            <td style="text-align:center;">
                                <input type="radio" wire:model="checkItems.{{ $key }}.status" value="ok">
                            </td>
                            <td style="text-align:center;">
                                <input type="radio" wire:model="checkItems.{{ $key }}.status" value="needs_attention">
                            </td>
                            <td style="text-align:center;">
                                <input type="radio" wire:model="checkItems.{{ $key }}.status" value="n_a">
                            </td>
                            <td>
                                @if(!empty($checkItems[$key]['status']) && $checkItems[$key]['status'] === 'needs_attention')
                                    <input type="text" wire:model="checkItems.{{ $key }}.remarks" placeholder="Describe the issue..." class="gh-input" style="width:100%;">
                                @else
                                    <input type="text" wire:model="checkItems.{{ $key }}.remarks" placeholder="Optional notes..." class="gh-input" style="width:100%;">
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
