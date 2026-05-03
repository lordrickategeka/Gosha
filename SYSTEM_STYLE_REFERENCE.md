# ISDTS System Style Reference (AI Replication Guide)

Use this file as the single source of truth when asking any AI to generate UI for this system.

## 1) Tech Stack and Styling Base

- Framework: Laravel Blade + Livewire
- Utility CSS: Tailwind CSS
- Component layer: DaisyUI
- Tailwind entry: `resources/css/app.css` only includes Tailwind layers + DaisyUI plugin

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
@plugin "daisyui";
```

## 2) Theme Tokens (Use Exact Values)

From `tailwind.config.js` and DaisyUI theme `bccflow`:

- `primary`: `#2C72B3`
- `primary-light`: `#4A8FCC`
- `primary-dark`: `#1E4F7F`
- `secondary`: `#5BA3E0`
- `secondary-light`: `#7DB9E8`
- `secondary-dark`: `#2E5F8F`
- `neutral`: `#2B3440`
- `base-100`: `#ffffff`
- `success`: `#36D399`
- `warning`: `#FBBD23`
- `error`: `#F87272`

Typography base:

- Primary font stack starts with `Figtree`

## 3) Visual Language Rules

- Use clean, flat UI (no gradients).
- Prefer white cards on light gray page backgrounds.
- Keep icons black for consistency (`text-black`).
- Card corners: `rounded-lg` or `rounded-xl`.
- Card elevation: `shadow-md` (avoid heavy shadows).
- Borders: subtle (`border border-gray-200`).

## 4) Layout and Spacing Standards

- Page/container padding: `p-4` or `p-6`
- Section spacing: `mb-6` or `mb-8`
- Small element spacing: `mb-4`
- Grid gaps: `gap-4` or `gap-6`
- Responsive grid baseline: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3`

## 5) Core Component Recipes

### 5.1 Cards

```html
<div class="card bg-base-100 shadow-md rounded-lg border border-gray-200">
  <div class="card-body p-4">
    <h3 class="text-black font-bold">Card title</h3>
    <p class="text-gray-700">Card content</p>
  </div>
</div>
```

### 5.2 Buttons

- Primary action: `btn btn-primary`
- Secondary action: `btn btn-secondary`
- Outline action: `btn btn-outline btn-primary`
- Icon-only action: `btn btn-ghost btn-square` + black icon

### 5.3 Forms

- Labels: `text-xs font-medium text-gray-700`
- Inputs/selects: `border border-gray-300 rounded-lg`
- Focus ring: `focus:ring-2 focus:ring-blue-500 focus:border-transparent`
- Compact control sizing is common: `text-xs px-2 py-1.5` or `px-4 py-1.5`

### 5.4 Tables

Container:

```html
<div class="bg-white rounded-lg shadow-md overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full">
      <!-- ... -->
    </table>
  </div>
</div>
```

Header:

- `thead`: `bg-gray-50 border-b border-gray-200`
- `th`: `px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider`

Rows/cells:

- Zebra rows: even `bg-white`, odd `bg-gray-200`
- Hover: `hover:bg-blue-200 transition-colors duration-150`
- Cell text: mostly `text-xs text-gray-900`
- Include index column `#` as the first column

Status badges:

- Success: `bg-green-100 text-green-800`
- Error/inactive: `bg-red-100 text-red-800`
- Warning: `bg-yellow-100 text-yellow-800`
- Info: `bg-blue-100 text-blue-800`

Action icons:

- View: `text-blue-600 hover:text-blue-900`
- Edit: `text-green-600 hover:text-green-900`
- Delete: `text-red-600 hover:text-red-900`
- Size: `w-4 h-4`

### 5.5 Search/Filter Bars Above Tables

Use compact controls with icons and borders:

- Search input: `w-full px-4 py-1.5 pl-9 text-xs border border-gray-300 rounded-lg`
- Small buttons: `px-2 py-1.5 border border-gray-300 rounded-lg text-xs text-gray-700 hover:bg-gray-50`
- Per-page select options usually include: `6, 10, 25, 50, 100`

### 5.6 Modals

Root wrapper:

- `fixed inset-0 z-50 overflow-y-auto` + `role="dialog" aria-modal="true"`

Backdrop variants:

- Standard: `fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity`
- Import workflow tint: `fixed inset-0 bg-blue-100 opacity-30 transition-opacity`

Panel:

- `inline-block align-bottom bg-base-100 rounded-lg text-left overflow-hidden shadow-md transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full`
- Use `sm:max-w-2xl` for wider import/bulk forms

Footer actions:

- `bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2`

## 6) Typography and Icon Sizing

- Headings:
  - H1: `text-3xl font-bold text-black`
  - H2: `text-2xl font-bold text-black`
  - H3: `text-xl font-bold text-black`
- Body: `text-gray-700`
- Muted: `text-gray-500`
- Common icon sizes:
  - Small: `w-5 h-5`
  - Medium: `w-6 h-6`
  - Large: `w-8 h-8`

## 7) Accessibility Requirements

- Keep visible focus state (`focus:ring-2 focus:ring-primary` or blue ring variant).
- Modal keyboard close on Escape.
- Backdrop click closes modal.
- Use semantic buttons for actions.
- Maintain readable contrast for text and badges.

## 8) AI Prompt Template (Copy/Paste)

Use this prompt when asking another AI to create UI for this project:

```text
Build this UI using Tailwind CSS + DaisyUI and match ISDTS style exactly.

Mandatory style rules:
- Use color tokens: primary #2C72B3, primary-light #4A8FCC, primary-dark #1E4F7F, secondary #5BA3E0, neutral #2B3440, base-100 #ffffff.
- Flat design only (no gradients).
- Cards: bg-base-100, rounded-lg/rounded-xl, shadow-md, border border-gray-200.
- Icons should be black (text-black) unless status/action coloring is required.
- Buttons should use DaisyUI classes (btn btn-primary, btn btn-secondary, btn btn-outline btn-primary).
- Tables must use: header bg-gray-50 border-b border-gray-200; compact text-xs; zebra rows (white/gray-200); row hover blue-200.
- Search/filter bars should use compact inputs and buttons (text-xs, py-1.5).
- Modals: fixed overlay + rounded card panel; support Escape and backdrop close.
- Keep spacing compact and consistent: p-4/p-6, gap-4/gap-6, mb-4/mb-6/mb-8.

Return clean, production-ready Blade/HTML with Tailwind/DaisyUI classes only.
```

## 9) Things to Avoid

- Do not introduce different color systems or random accent colors.
- Do not use heavy shadows or glassmorphism.
- Do not use gradient backgrounds.
- Do not switch to Bootstrap utility/component classes for new Tailwind/DaisyUI screens.
- Do not mix oversized typography into dense data-table screens.
