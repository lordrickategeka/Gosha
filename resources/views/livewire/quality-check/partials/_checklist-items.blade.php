{{-- Quality Checklist Items Partial --}}
{{-- Displays grouped quality check items organized by section with radio status selectors --}}

@foreach ($this->groupedItems as $sectionKey => $sectionName)
    <div class="bg-base-100 rounded-lg shadow mb-6">
        <div class="bg-primary text-primary-content p-4 rounded-t-lg">
            <h2 class="text-lg font-bold">{{ $sectionName }}</h2>
            @if($sectionKey === 'road_test' && $requiresRoadTest)
                <span class="badge badge-error badge-sm">Mandatory</span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th class="w-2/5">Item</th>
                        <th class="w-1/6 text-center">OK</th>
                        <th class="w-1/6 text-center">Needs Attention</th>
                        <th class="w-1/6 text-center">N/A</th>
                        <th class="w-1/3">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (collect($checkItems)->where('section', $sectionKey) as $key => $item)
                        <tr>
                            <td>{{ $item['item_name'] }}</td>
                            <td class="text-center">
                                <input type="radio"
                                       wire:model="checkItems.{{ $key }}.status"
                                       value="ok"
                                       class="radio radio-success">
                            </td>
                            <td class="text-center">
                                <input type="radio"
                                       wire:model="checkItems.{{ $key }}.status"
                                       value="needs_attention"
                                       class="radio radio-warning">
                            </td>
                            <td class="text-center">
                                <input type="radio"
                                       wire:model="checkItems.{{ $key }}.status"
                                       value="n_a"
                                       class="radio radio-ghost">
                            </td>
                            <td>
                                @if(!empty($checkItems[$key]['status']) && $checkItems[$key]['status'] === 'needs_attention')
                                    <input type="text"
                                           wire:model="checkItems.{{ $key }}.remarks"
                                           placeholder="Describe the issue..."
                                           class="input input-sm input-bordered w-full">
                                @else
                                    <input type="text"
                                           wire:model="checkItems.{{ $key }}.remarks"
                                           placeholder="Optional notes..."
                                           class="input input-sm input-bordered input-ghost w-full">
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
