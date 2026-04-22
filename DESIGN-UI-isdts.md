# ISDTS Core - Design UI Guidelines

## Overview
This document captures the design consistency standards for the ISDTS Core application using DaisyUI components with Tailwind CSS.

## Color Palette

### Primary Colors
- **Primary**: `#2C72B3` - Main brand color, used for primary actions and key UI elements
- **Primary Light**: `#4A8FCC` - Hover states and lighter variants
- **Primary Dark**: `#1E4F7F` - Active states and darker variants

### Secondary Colors
- **Secondary**: `#5BA3E0` - Supporting blue shade
- **Secondary Light**: `#7DB9E8` - Lighter accents
- **Secondary Dark**: `#2E5F8F` - Darker accents

### Neutral Colors
- **Base**: `#ffffff` - Background color
- **Neutral**: `#2B3440` - Text and borders
- **Black Icons**: Use `text-black` or `text-neutral` for all icons

## Design Principles

### 1. Modern & Clean
- Use ample whitespace for breathing room
- Clean typography with clear hierarchy
- Focus on readability and usability

### 2. No Gradients
- Avoid gradient backgrounds or overlays
- Use solid colors only
- Maintain flat design aesthetic

### 3. Card Edges
- All cards should use rounded corners: `rounded-lg` or `rounded-xl`
- Consistent shadow usage: `shadow-md` for elevated elements
- Border usage: subtle borders with `border border-gray-200`

### 4. Icons
- **Always use black icons** (`text-black`)
- Icon size consistency: `w-5 h-5` for small, `w-6 h-6` for medium, `w-8 h-8` for large
- Use solid style icons preferably

## Component Standards

### Cards
```html
<!-- Standard Card -->
<div class="card bg-base-100 shadow-md rounded-lg border border-gray-200">
  <div class="card-body">
    <!-- Content -->
  </div>
</div>

<!-- Card with actions -->
<div class="card bg-base-100 shadow-md rounded-xl">
  <div class="card-body">
    <h2 class="card-title text-black">Title</h2>
    <p>Content here</p>
    <div class="card-actions justify-end">
      <button class="btn btn-primary">Action</button>
    </div>
  </div>
</div>
```

### Buttons
```html
<!-- Primary Button -->
<button class="btn btn-primary">Primary Action</button>

<!-- Secondary Button -->
<button class="btn btn-secondary">Secondary Action</button>

<!-- Outline Button -->
<button class="btn btn-outline btn-primary">Outline</button>

<!-- Icon Button -->
<button class="btn btn-ghost btn-square">
  <svg class="w-5 h-5 text-black"><!-- icon --></svg>
</button>
```

### Navigation/Sidebar
- Use `drawer` component for responsive sidebar
- Sidebar background: `bg-base-100`
- Active menu items: `bg-primary text-white`
- Inactive menu items: `text-black hover:bg-gray-100`
- Icons in menu: `text-black w-5 h-5`

### Dashboard Layout
- Use grid system: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4`
- Stat cards: Use DaisyUI `stats` component
- Consistent padding: `p-4` or `p-6` for main containers

### Typography
- Headings: Use `text-black font-bold`
  - H1: `text-3xl font-bold`
  - H2: `text-2xl font-bold`
  - H3: `text-xl font-bold`
- Body text: `text-gray-700`
- Muted text: `text-gray-500`

### Spacing
- Section spacing: `mb-6` or `mb-8`
- Element spacing: `mb-4`
- Grid gaps: `gap-4` or `gap-6`
- Card padding: `p-4` or `p-6`

### Shadows & Borders
- Elevated cards: `shadow-md`
- Hover states: `hover:shadow-lg`
- Borders: `border border-gray-200` for subtle definition
- No heavy shadows or drop shadows

## DaisyUI Components Usage

### Preferred Components
- **Cards**: For content containers
- **Buttons**: For all actions
- **Stats**: For dashboard metrics
- **Drawer**: For sidebar navigation
- **Menu**: For navigation items
- **Badge**: For status indicators
- **Alert**: For notifications
- **Modal**: For dialogs
- **Table**: For data display
- **Form Controls**: Input, Select, Checkbox, etc.

### Component Customization
Always maintain:
- Black icons
- Primary/Secondary color scheme
- Rounded corners on cards
- No gradients
- Clean, modern spacing

## Responsive Design
- Mobile-first approach
- Breakpoints:
  - sm: 640px
  - md: 768px
  - lg: 1024px
  - xl: 1280px
- Hide/show elements: `hidden md:block` or `block md:hidden`
- Responsive grid: `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`

## Accessibility
- Proper contrast ratios for text
- Focus states: `focus:ring-2 focus:ring-primary`
- ARIA labels for interactive elements
- Keyboard navigation support

## Examples

### Dashboard Card
```html
<div class="card bg-base-100 shadow-md rounded-lg border border-gray-200">
  <div class="card-body">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-gray-500 text-sm">Total Users</p>
        <h3 class="text-3xl font-bold text-black">1,234</h3>
      </div>
      <svg class="w-8 h-8 text-black"><!-- icon --></svg>
    </div>
  </div>
</div>
```

### Sidebar Menu Item
```html
<li>
  <a class="flex items-center gap-3 text-black hover:bg-gray-100 rounded-lg">
    <svg class="w-5 h-5 text-black"><!-- icon --></svg>
    <span>Dashboard</span>
  </a>
</li>
```

## Notes
- Always test components in both light mode
- Maintain consistency across all pages
- Update this document when new patterns emerge
- Review designs for adherence to these guidelines before implementation
