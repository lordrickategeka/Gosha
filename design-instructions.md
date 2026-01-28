Design Elements:
-tailwind -->lets use https://daisyui.com/
-Clean, minimal color scheme using primarily grays, blacks, and whites
-Professional typography and spacing
-Subtle shadows and borders for depth
-Responsive grid layout

## Dashboard Layout Usage

- Use the `layouts.dash-layout` Blade layout for all dashboard pages:
  ```blade
  <x-layouts.dash-layout title="Page Title">
      <!-- Page content here -->
  </x-layouts.dash-layout>
  ```
- The layout includes a header (`<x-header />`) and sidebar (`<x-sidebar />`) as Blade components for consistency.
- Place main page content inside the layout slot.
- To add custom scripts or styles, use Blade's `@push('head')` and `@push('scripts')` stacks.
- All dashboard pages should use Tailwind utility classes and follow the minimal, professional style established in the provided HTML designs.

### Example
```blade
<x-layouts.dash-layout title="Dashboard">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Cards, charts, etc. -->
    </div>
</x-layouts.dash-layout>
```

### Component Locations
- Layout: `resources/views/layouts/dash-layout.blade.php`
- Header: `resources/views/components/header.blade.php`
- Sidebar: `resources/views/components/sidebar.blade.php`
