<div>
    <x-layouts.dash-layout title="Create Workshop Jobcard">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 w-full">
                <h2 class="text-lg font-bold mb-4">Workshop Jobcard Details</h2>

                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-50 text-green-800 rounded">{{ session('success') }}</div>
                @endif

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Job Card</label>
                    <div class="mt-1 text-sm text-gray-900">
                        @if($jobCard)
                            <div class="font-semibold">{{ $jobCard->job_card_number }} - {{ $jobCard->number_plate }}</div>
                        @else
                            <div class="text-gray-500">No job card selected</div>
                        @endif
                    </div>
                </div>

                

                {{-- <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes / Instructions</label>
                    <textarea id="notes" wire:model.defer="notes" rows="4" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                </div> --}}

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="md:col-span-1">
                        <h3 class="font-semibold mb-2">Service Category</h3>
                        <div>
                            <label for="service_type" class="sr-only">Service Type</label>
                            <select id="service_type" wire:model="selectedServiceType" class="block w-full border-gray-300 rounded-md text-sm p-2">
                                <option value="">-- Select service type --</option>
                                @foreach($serviceTypes as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                            <button wire:click.prevent="addSelectedServiceType" class="mt-2 w-full px-2 py-1 bg-blue-600 text-white text-xs rounded">Add</button>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <h3 class="font-semibold mb-2">Mechanical Works</h3>
                        @error('mechanicalWorks') <div class="text-red-600 text-sm mb-2">{{ $message }}</div> @enderror
                        <div class="space-y-3">
                            @foreach($mechanicalWorks as $serviceId => $items)
                                <div class="p-3 border rounded" wire:key="mw-group-{{ $serviceId }}">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="text-sm font-medium">
                                            {{ optional($serviceTypes->firstWhere('id', (int)$serviceId))->name ?? 'Service' }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button wire:click.prevent="addItemToGroup({{ $serviceId }})" class="px-2 py-1 bg-green-600 text-white text-xs rounded">Add item</button>
                                            <button wire:click.prevent="removeMechanicalWork({{ $serviceId }})" class="text-red-600 text-sm">Remove group</button>
                                        </div>
                                    </div>

                                    @foreach($items as $itemIndex => $item)
                                        <div wire:key="mw-{{ $serviceId }}-{{ $itemIndex }}" class="mb-3 p-3 bg-gray-50 rounded">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-2">
                                                <div class="relative">
                                                    <label class="text-xs text-gray-600">Repair Items</label>
                                                    <input type="text" wire:model.debounce.300ms="mechanicalWorks.{{ $serviceId }}.{{ $itemIndex }}.repair_items" wire:keyup.debounce.400ms="searchRepairItems({{ $serviceId }}, {{ $itemIndex }})" class="mt-1 block w-full border-gray-300 rounded-md text-sm" autocomplete="off" />
                                                    @error('mechanicalWorks.'.$serviceId.'.'.$itemIndex.'.repair_items') <div class="text-red-600 text-xs">{{ $message }}</div> @enderror

                                                    @if(!empty($searchResults[$serviceId][$itemIndex] ?? []))
                                                        <ul class="absolute z-50 bg-white border rounded mt-1 w-full max-h-40 overflow-auto">
                                                            @foreach($searchResults[$serviceId][$itemIndex] as $res)
                                                                <li wire:click.prevent="chooseRepairItem({{ $serviceId }}, {{ $itemIndex }}, '{{ addslashes($res) }}')" class="px-2 py-1 hover:bg-gray-100 cursor-pointer text-sm">{{ $res }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-600">Quantity</label>
                                                    <input type="number" min="1" wire:model.defer="mechanicalWorks.{{ $serviceId }}.{{ $itemIndex }}.quantity" class="mt-1 block w-full border-gray-300 rounded-md text-sm" />
                                                    @error('mechanicalWorks.'.$serviceId.'.'.$itemIndex.'.quantity') <div class="text-red-600 text-xs">{{ $message }}</div> @enderror
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-600">Notes</label>
                                                    <input type="text" wire:model.defer="mechanicalWorks.{{ $serviceId }}.{{ $itemIndex }}.notes" class="mt-1 block w-full border-gray-300 rounded-md text-sm" />
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <button wire:click.prevent="removeMechanicalWork({{ $serviceId }}, {{ $itemIndex }})" class="text-red-600 text-sm">Remove item</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            @if(count($mechanicalWorks) === 0)
                                <div class="text-sm text-gray-500">No mechanical works added yet. Select a service type and click Add.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <button wire:click.prevent="save" class="px-4 py-2 bg-yellow-500 text-white rounded-md">Save Workshop Jobcard</button>
                    <a href="{{ route('job-cards.index') }}" class="px-4 py-2 border rounded-md text-sm">Cancel</a>
                </div>
            </div>
        </div>
    </x-layouts.dash-layout>
</div>
