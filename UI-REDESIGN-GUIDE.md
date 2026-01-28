# UI Redesign Implementation Guide

## Overview
This document provides examples of how components have been updated to follow the DESIGN-UI.md and MODAL-UI.md standards.

## What Has Been Updated

### 1. Tailwind Configuration
- Added primary color: `#2C72B3` with light and dark variants
- Added secondary color: `#5BA3E0` with light and dark variants
- Configured DaisyUI theme to use these colors

### 2. Core Components

#### Sidebar (`resources/views/components/sidebar.blade.php`)
- Updated to use `bg-base-100` instead of `bg-white`
- Black icons with `w-5 h-5` sizing
- Active state uses `bg-primary text-white`
- Inactive state uses `text-black hover:bg-gray-100`

#### Header (`resources/views/components/header.blade.php`)
- Logo background uses `bg-primary`
- Black icons throughout
- DaisyUI dropdown component for user menu
- Focus states use `focus:ring-primary`

#### Dashboard (`resources/views/livewire/dashboard-component.blade.php`)
- Stats cards use DaisyUI `card` component with `shadow-md rounded-lg`
- Text colors: `text-black` for headings, `text-gray-500` for labels
- Icons sized at `w-8 h-8 text-black`
- Badge components for status indicators
- Primary color accents on borders

#### Buttons
- Primary: Uses `btn btn-primary` with `bg-primary hover:bg-primary-dark`
- Secondary: Uses `btn btn-outline` with proper border and hover states
- All focus states use `focus:ring-2 focus:ring-primary`

### 3. New Modal System

Created reusable modal components following MODAL-UI guidelines:

#### `<x-ui-modal>`
Main modal wrapper with options for:
- `show`: Boolean to control visibility
- `maxWidth`: sm, md, lg, xl, 2xl, 3xl
- `closeMethod`: Wire method name to close modal
- `overlayType`: 'neutral' (gray) or 'import' (light-blue)

#### `<x-modal-header>`
- Black title text
- Close button with X icon
- Proper spacing and borders

#### `<x-modal-body>`
- Main content area with proper padding

#### `<x-modal-footer>`
- Gray background
- Flex layout with reverse order (primary action on right)

### 4. Usage Examples

#### Example: Dashboard Card
```blade
<div class="card bg-base-100 shadow-md rounded-lg border border-gray-200">
    <div class="card-body p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">Total Users</p>
                <h3 class="text-3xl font-bold text-black">1,234</h3>
            </div>
            <i class="fas fa-users w-8 h-8 text-black"></i>
        </div>
    </div>
</div>
```

#### Example: Modal Usage
```blade
<x-ui-modal :show="$showModal" closeMethod="closeModal" maxWidth="lg">
    <x-modal-header closeMethod="closeModal">
        Add New Customer
    </x-modal-header>
    
    <x-modal-body>
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-700">Name</label>
                    <input type="text" 
                           wire:model="name" 
                           class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-primary" />
                </div>
                <!-- More fields -->
            </div>
        </form>
    </x-modal-body>
    
    <x-modal-footer>
        <x-primary-button wire:click="save">Save</x-primary-button>
        <x-secondary-button wire:click="closeModal">Cancel</x-secondary-button>
    </x-modal-footer>
</x-ui-modal>
```

#### Example: Import Modal (Light Blue Overlay)
```blade
<x-ui-modal :show="$showImportModal" 
            closeMethod="closeImportModal" 
            maxWidth="2xl"
            overlayType="import">
    <!-- Modal content -->
</x-ui-modal>
```

#### Example: Table with Design Standards
```blade
<div class="card bg-base-100 shadow-md rounded-lg border border-gray-200 overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-black">John Doe</td>
                <td class="px-4 py-3">
                    <span class="badge badge-success badge-sm">Active</span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

#### Example: Form Buttons
```blade
<div class="flex gap-2">
    <x-primary-button wire:click="save">
        <i class="fas fa-save w-5 h-5 mr-2 text-white"></i>
        Save Changes
    </x-primary-button>
    <x-secondary-button wire:click="cancel">
        Cancel
    </x-secondary-button>
</div>
```

## Next Steps for Full Implementation

### Components Needing Updates:
1. **Service Types Component** - Update forms, tables, and buttons
2. **Staff Component** - Update forms, tables, and buttons  
3. **Customers Component** - Already has good structure, needs color/class updates
4. **Vehicle Types Component** - Update to match design standards
5. **Job Cards Component** - Update cards and forms
6. **Workshop Components** - Update UI elements

### Standard Updates Required:
- Replace `bg-white` with `bg-base-100`
- Replace `bg-blue-600` buttons with `btn btn-primary`
- Replace `bg-gray-300` buttons with `btn btn-outline`
- Update all icons to `text-black` with proper sizing (`w-5 h-5` for inline, `w-8 h-8` for large)
- Use DaisyUI `badge` component instead of custom badge classes
- Wrap tables in `card` component
- Update form inputs to use `focus:ring-2 focus:ring-primary`
- Replace heading colors from `text-gray-900` to `text-black`

### Form Input Standards:
```blade
<div>
    <label class="block text-xs font-medium text-gray-700 mb-1">Label</label>
    <input type="text" 
           class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
</div>
```

### Select Input Standards:
```blade
<select class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary">
    <option>Option 1</option>
</select>
```

## Design Checklist
- [ ] All cards use `card bg-base-100 shadow-md rounded-lg border border-gray-200`
- [ ] All icons are black (`text-black`)
- [ ] Primary actions use `btn btn-primary`
- [ ] Secondary actions use `btn btn-outline` or `btn btn-secondary`
- [ ] Headings use `text-black` not `text-gray-900`
- [ ] Form inputs have `focus:ring-2 focus:ring-primary`
- [ ] Status badges use DaisyUI `badge` component
- [ ] Tables wrapped in card component
- [ ] Modals use new modal components
- [ ] Active nav items use `bg-primary text-white`

## Color Reference Quick Guide
- Primary Action: `bg-primary hover:bg-primary-dark`
- Primary Text: `text-black`
- Secondary Text: `text-gray-500` or `text-gray-700`
- Card Background: `bg-base-100`
- Borders: `border-gray-200`
- Hover States: `hover:bg-gray-100` (for light backgrounds)
- Focus Rings: `focus:ring-2 focus:ring-primary`
