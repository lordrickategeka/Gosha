@props(['name', 'label', 'type' => 'text', 'required' => false, 'hint' => null, 'autocomplete' => null])
<div class="form-control" @if($type === 'password') x-data="{ visible: false }" @endif>
    <label for="{{ $name }}" class="label justify-start gap-1 font-medium">
        {{ $label }} <span class="text-xs font-normal text-base-content/60">{{ $required ? '(required)' : '(optional)' }}</span>
    </label>
    <div class="relative">
        <input id="{{ $name }}" name="{{ $name }}" wire:model="{{ $name }}" type="{{ $type }}"
               @if($type === 'password') x-bind:type="visible ? 'text' : 'password'" @endif
               @required($required) @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
               @if($type === 'tel') inputmode="tel" maxlength="50" @elseif($type !== 'password') maxlength="255" @endif
               aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
               aria-describedby="{{ $name }}-feedback"
               {{ $attributes->class(['input input-bordered w-full', 'pr-20' => $type === 'password', 'input-error' => $errors->has($name)]) }} />
        @if($type === 'password')
            <button type="button" class="absolute right-3 top-0 h-full text-sm link" x-on:click="visible = !visible" x-bind:aria-pressed="visible" aria-label="Show or hide {{ strtolower($label) }}" x-text="visible ? 'Hide' : 'Show'">Show</button>
        @endif
    </div>
    <div id="{{ $name }}-feedback" class="text-xs mt-1">
        @error($name) <span class="text-error" role="alert">{{ $message }}</span>
        @else @if($hint)<span class="text-base-content/60">{{ $hint }}</span>@endif @enderror
    </div>
</div>
