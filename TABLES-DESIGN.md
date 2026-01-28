# Tables Design Guidelines

This document outlines the consistent design standards for all data tables in the ISDTS Core application.

## Table Structure

All tables in the system should follow this standardized structure:

### Container
```blade
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <!-- Table content -->
        </table>
    </div>
</div>
```

### Table Head
```blade
<thead class="bg-gray-50 border-b border-gray-200">
    <tr>
        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Column Name</th>
        <!-- More columns -->
        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
    </tr>
</thead>
```

**Header Styling:**
- Background: `bg-gray-50`
- Border: `border-b border-gray-200`
- Padding: `px-4 py-2` (horizontal: 1rem, vertical: 0.5rem)
- Text: `text-xs font-medium text-gray-500 uppercase tracking-wider`
- Alignment: `text-left` for data columns, `text-right` for action columns

### Table Body
```blade
<tbody class="bg-white">
    @forelse ($items as $index => $item)
        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-200' }} hover:bg-blue-200 transition-colors duration-150">
            <td class="px-4 py-2 whitespace-nowrap">
                <div class="text-xs text-gray-900">{{ $items->firstItem() + $index }}</div>
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
                <div class="text-xs text-gray-900">{{ $item->name }}</div>
            </td>
            <!-- More cells -->
        </tr>
    @empty
        <tr>
            <td colspan="X" class="px-6 py-12 text-center">
                <!-- Empty state content -->
            </td>
        </tr>
    @endforelse
</tbody>
```

**Body Styling:**
- **Zebra Striping:** Even rows (`bg-white`), Odd rows (`bg-gray-200`)
- **Hover Effect:** `hover:bg-blue-200 transition-colors duration-150`
- **Cell Padding:** `px-4 py-2`
- **Text Size:** `text-xs`
- **Text Color:** Primary content `text-gray-900`, Secondary content `text-gray-700`

### Iteration Column

Every table must include a **#** column as the first column to display row numbers:

#### For Paginated Tables:
```blade
<td class="px-4 py-2 whitespace-nowrap">
    <div class="text-xs text-gray-900">{{ $items->firstItem() + $index }}</div>
</td>
```

#### For Non-Paginated Tables:
```blade
<td class="px-4 py-2 whitespace-nowrap">
    <div class="text-xs text-gray-900">{{ $loop->iteration }}</div>
</td>
```

## Status Badges

For status columns, use consistent badge styling:

```blade
<span class="px-2 inline-flex text-xs leading-4 font-semibold rounded-full
    {{ $item->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
    {{ ucfirst($item->status) }}
</span>
```

**Status Colors:**
- **Active/Success:** `bg-green-100 text-green-800`
- **Inactive/Error:** `bg-red-100 text-red-800`
- **Warning:** `bg-yellow-100 text-yellow-800`
- **Info:** `bg-blue-100 text-blue-800`

## Action Buttons

Action columns should be right-aligned with icon-based buttons:

```blade
<td class="px-4 py-2 whitespace-nowrap text-right text-xs font-medium">
    <div class="flex items-center justify-end gap-2">
        <button wire:click="view({{ $item->id }})" class="text-blue-600 hover:text-blue-900" title="View">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <!-- SVG path -->
            </svg>
        </button>
        <button wire:click="edit({{ $item->id }})" class="text-green-600 hover:text-green-900" title="Edit">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <!-- SVG path -->
            </svg>
        </button>
        <button wire:click="delete({{ $item->id }})" 
            wire:confirm="Are you sure you want to delete this item?"
            class="text-red-600 hover:text-red-900" title="Delete">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <!-- SVG path -->
            </svg>
        </button>
    </div>
</td>
```

**Action Button Colors:**
- **View:** `text-blue-600 hover:text-blue-900`
- **Edit:** `text-green-600 hover:text-green-900`
- **Delete:** `text-red-600 hover:text-red-900`
- **Icon Size:** `w-4 h-4`

## Empty State

When no data is available, display a centered empty state:

```blade
@empty
    <tr>
        <td colspan="X" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <!-- Relevant icon -->
                </svg>
                <p class="text-lg font-medium">No items found</p>
                <p class="text-sm">Create your first item to get started</p>
            </div>
        </td>
    </tr>
@endforelse
```

## Pagination

For paginated tables, add pagination links below the table with a default of **6 items per page**:

```blade
@if ($items->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $items->links() }}
    </div>
@endif
```

**Pagination Settings:**
- Default items per page: `6`
- Per-page options: `6, 10, 25, 50, 100`
- Theme: `tailwind`
- Reset page on search: Yes (use `updatingSearch()` method)

## Search and Filter Bar

Tables with search functionality should include a filter bar above the table with Filter, Import, and Export actions:

```blade
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="flex-1 w-full">
            <div class="relative">
                <input type="text"
                    wire:model.live.debounce.300ms="search"
                    class="w-full px-4 py-1.5 pl-9 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Search...">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <!-- Filter Button -->
            <button class="px-2 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-1.5 text-xs text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
            <!-- Import Button -->
            <button class="px-2 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-1.5 text-xs text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Import
            </button>
            <!-- Export Button -->
            <button class="px-2 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-1.5 text-xs text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
            </button>
            <label class="text-xs text-gray-600">Show:</label>
            <select wire:model.live="perPage" class="px-2 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="6">6</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>
</div>
```

**Search Bar Styling:**
- Input: `px-4 py-1.5 pl-9 text-xs` with border and focus states
- Search icon: `w-4 h-4` positioned at `left-2.5 top-2.5`
- Action buttons: `px-2 py-1.5 text-xs` with `w-3.5 h-3.5` icons
- Per-page selector: `px-2 py-1.5 text-xs`
- Label: `text-xs`

## Typography

**Text Sizes:**
- Table headers: `text-xs`
- Table cells: `text-xs`
- Empty state title: `text-lg`
- Empty state description: `text-sm`

**Font Weights:**
- Headers: `font-medium`
- Primary content: `font-medium`
- Regular content: Default weight
- Status badges: `font-semibold`

## Spacing

**Padding:**
- Table cells: `px-4 py-2`
- Empty state: `px-6 py-12`
- Pagination container: `px-6 py-4`

**Gaps:**
- Action buttons: `gap-2`
- Filter bar elements: `gap-4`

## Colors Reference

**Backgrounds:**
- Table header: `bg-gray-50`
- Even rows: `bg-white`
- Odd rows: `bg-gray-200`
- Hover: `bg-blue-200`

**Borders:**
- Header bottom: `border-b border-gray-200`
- Pagination top: `border-t border-gray-200`

**Text:**
- Headers: `text-gray-500`
- Primary content: `text-gray-900`
- Secondary content: `text-gray-700`
- Empty state: `text-gray-500`
- Empty state icon: `text-gray-300`

## Implementation Checklist

When creating a new table, ensure:

- [ ] Container has `bg-white rounded-lg shadow-md overflow-hidden`
- [ ] Table has `overflow-x-auto` wrapper
- [ ] Headers use `px-4 py-2 text-xs` with proper alignment
- [ ] First column is `#` with iteration numbers
- [ ] Zebra striping is implemented with even/odd rows
- [ ] Hover effect shows `bg-blue-200`
- [ ] All cells use `px-4 py-2 text-xs`
- [ ] Status badges follow color conventions
- [ ] Action buttons use `w-4 h-4` icons with proper colors
- [ ] Empty state is centered and styled correctly
- [ ] Pagination is included for paginated lists
- [ ] Search bar follows standard layout (if applicable)
- [ ] Colspan matches total column count in empty state

## Examples

### Currently Implemented Tables:
1. **Service Types** (`resources/views/livewire/service-types/service-types-component.blade.php`)
2. **Clients List** (`resources/views/livewire/clients/clients-list-component.blade.php`)
3. **Form Submissions** (`resources/views/livewire/forms/form-submissions-component.blade.php`)

All future tables should follow these standards to maintain visual consistency across the application.
