@props([
    'show' => false,
    'maxWidth' => 'lg',
    'closeMethod' => 'closeModal',
    'title' => '',
    'overlayType' => 'neutral', // 'neutral' or 'import' (light-blue)
])

@php
$maxWidthClass = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    '3xl' => 'sm:max-w-3xl',
][$maxWidth];

$overlayClass = $overlayType === 'import' 
    ? 'fixed inset-0 bg-blue-100 opacity-30 transition-opacity' 
    : 'fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity';
@endphp

@if ($show)
  <div class="fixed inset-0 z-50 overflow-y-auto" 
       role="dialog" 
       aria-modal="true" 
       aria-labelledby="modal-title"
       wire:keydown.escape="{{ $closeMethod }}">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Backdrop -->
      <div class="{{ $overlayClass }}" wire:click="{{ $closeMethod }}"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-base-100 rounded-lg text-left overflow-hidden shadow-md transform transition-all sm:my-8 sm:align-middle {{ $maxWidthClass }} sm:w-full border border-gray-200">
        {{ $slot }}
      </div>
    </div>
  </div>
@endif
