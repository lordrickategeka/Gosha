<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-primary transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
