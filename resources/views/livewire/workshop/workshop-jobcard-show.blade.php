<div>
    <x-layouts.dash-layout title="Workshop Jobcard">
        <div class="max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="text-lg font-bold mb-4">Workshop Jobcard #{{ $workshopJobcard->id }}</h2>
                <h2 class="text-lg font-bold mb-4">Workshop Jobcard Number: {{ $workshopJobcard->workshop_jobcard_number }}</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Notes / Instructions</label>
                    <textarea wire:model.defer="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                </div>

                <h3 class="text-md font-semibold mb-4">Associated Jobcard: #{{ $workshopJobcard->jobcard->job_card_number}}</h3>

                <h3 class="font-semibold mb-2">Mechanical Works</h3>
                <div class="space-y-3">
                    @foreach($mechanicalWorks as $id => $mw)
                        <div class="p-3 border rounded">
                            <div class="text-sm font-medium mb-2">{{ optional($workshopJobcard->mechanicalWorks->firstWhere('id', $id)->serviceType)->name }}</div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                <div>
                                    <label class="text-xs text-gray-600">Repair Items</label>
                                    <input type="text" wire:model.defer="mechanicalWorks.{{ $id }}.repair_items" class="mt-1 block w-full border-gray-300 rounded-md text-sm" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-600">Quantity</label>
                                    <input type="number" min="1" wire:model.defer="mechanicalWorks.{{ $id }}.quantity" class="mt-1 block w-full border-gray-300 rounded-md text-sm" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-600">Notes</label>
                                    <input type="text" wire:model.defer="mechanicalWorks.{{ $id }}.notes" class="mt-1 block w-full border-gray-300 rounded-md text-sm" />
                                </div>
                            </div>
                            <div class="mt-2 text-right">
                                <button wire:click.prevent="deleteMechanicalWork({{ $id }})" class="text-red-600 text-sm">Delete</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex items-center space-x-2">
                    <button wire:click.prevent="updateAll" class="px-4 py-2 bg-blue-600 text-white rounded-md">Save Changes</button>
                    <a href="{{ route('work-orders.index') }}" class="px-4 py-2 border rounded-md text-sm">Back</a>
                </div>
            </div>
        </div>
    </x-layouts.dash-layout>
</div>
