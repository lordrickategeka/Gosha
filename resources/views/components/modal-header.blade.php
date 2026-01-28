@props(['closeMethod' => 'closeModal'])

<div class="bg-base-100 px-6 py-4 border-b border-gray-200">
    <div class="flex justify-between items-center">
        <h3 id="modal-title" class="text-lg font-medium text-black">
            {{ $slot }}
        </h3>
        <button type="button" 
                wire:click="{{ $closeMethod }}" 
                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary rounded-lg">
            <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
