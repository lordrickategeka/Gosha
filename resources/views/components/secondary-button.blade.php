<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-black hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
