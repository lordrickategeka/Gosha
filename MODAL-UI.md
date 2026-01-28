# Modal UI Guidelines

This file documents the project's modal/dialog patterns so new modals match the existing `Projects` modal and the Import modal design you requested.

Goals
- Consistent structure and spacing across modals
- Accessible dialogs (ARIA, keyboard close)
- Use the project's design tokens and component conventions from `DESIGN-UI.md`
- Provide both a neutral (gray) backdrop and a light-blue-tinted backdrop variant used for import overlays

Basic modal anatomy
1. Root wrapper (rendered conditionally)
   - `fixed inset-0 z-50 overflow-y-auto` with `role="dialog" aria-modal="true"`
   - An inner centering container: `flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0`
2. Backdrop (click-to-close)
   - Neutral example: `<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>`
   - Light-blue import overlay: `<div class="fixed inset-0 bg-blue-100 opacity-30 transition-opacity" wire:click="closeModal"></div>`
   - Backdrop should be the first child so it sits beneath the panel.
3. Modal panel (the card)
   - Use the project's card style: `inline-block align-bottom bg-base-100 rounded-lg text-left overflow-hidden shadow-md transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full`
   - For wider modals (import, bulk actions) use `sm:max-w-2xl` or `sm:max-w-3xl`.
4. Header
   - Title: `h3` with `text-lg font-medium text-black`
   - Close button: small `X` in the header; `button wire:click="closeModal" class="text-gray-400 hover:text-gray-600"` with a black icon (`text-black` per design)
5. Body
   - Content area: typically `bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4` or directly `p-6` inside the card
   - Form layout with `grid grid-cols-1 md:grid-cols-2 gap-3` etc.
   - Labels: `text-xs font-medium text-gray-700` and inputs `border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500` following `DESIGN-UI.md`.
6. Footer / actions
   - Footer container: `bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2`
   - Primary action: `bg-primary hover:bg-primary-dark text-white rounded-lg px-4 py-2` (or use `btn btn-primary` if DaisyUI available)
   - Secondary: `bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg px-4 py-2`

Accessibility
- `role="dialog" aria-modal="true"` on the root wrapper.
- Provide an accessible title (e.g. `aria-labelledby="modal-title"`) where relevant.
- Close on backdrop click (wire:click="closeModal") and on `Esc` key. Example Livewire hookup (Alpine or plain JS):
  - In Blade: `wire:keydown.escape="closeModal"` on the root wrapper, or with Alpine: `x-on:keydown.window.escape="close = false"`.
- Focus management: when opening a modal, focus the first interactive control and trap focus until close. For robust trapping use an small helper or package (e.g. focus-trap).

Modal styling choices used in the repo
- Card: `bg-base-100 shadow-md rounded-lg border border-gray-200`
- Heading: `text-black` (icons should be black per design guidelines)
- Buttons: primary uses `bg-primary` (Design: `#2C72B3`) — prefer Tailwind/Tokens if configured.

Project modal (existing example)
- Wrapper + backdrop:

```blade
@if ($showCreateModal)
  <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeCreateModal"></div>

      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <!-- header, body, footer -->
      </div>
    </div>
  </div>
@endif
```

- Footer style: `bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2`.

Import modal (recommended pattern — matches project modal, but with light-blue overlay)
- Use this structure but swap the backdrop color to a light-blue tint:

```blade
@if ($showImportModal)
  <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="import-modal-title" role="dialog" aria-modal="true" wire:keydown.escape="closeImportModal">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- light-blue overlay -->
      <div class="fixed inset-0 bg-blue-100 opacity-30 transition-opacity" wire:click="closeImportModal"></div>

      <div class="inline-block align-bottom bg-base-100 rounded-lg text-left overflow-hidden shadow-md transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
        <form wire:submit.prevent="importConfirm">
          <div class="bg-base-100 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex justify-between items-center mb-4">
              <h3 id="import-modal-title" class="text-lg font-medium text-black">Import Customers</h3>
              <button type="button" wire:click="closeImportModal" class="text-gray-400 hover:text-gray-600">
                <!-- X icon (black) -->
              </button>
            </div>

            <!-- body: radio for mode, optional project select, file input -->
          </div>

          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-primary text-white rounded-lg">Start Import</button>
            <button type="button" wire:click="closeImportModal" class="mt-3 sm:mt-0 w-full sm:w-auto px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endif
```

Wire/Livewire patterns and methods
- `public $showModal = false;` toggles the modal
- `public function openModal() { $this->showModal = true; }`
- `public function closeModal() { $this->showModal = false; }`
- For the Import case we used:
  - `public $showImportModal = false;`
  - `public function openImportModal() { $this->showImportModal = true; }`
  - `public function closeImportModal() { $this->showImportModal = false; $this->importFile = null; ... }`
  - `public function importConfirm() { /* validate modal inputs and process file/upload job */ }`

File uploads
- Use `use WithFileUploads;` on the Livewire component.
- Validate the file: `"importFile" => 'required|file|mimes:csv,txt'` (or allow xlsx if implementing that parser)
- For large imports delegate parsing to a queued job: store the uploaded file with `$this->importFile->store('imports')` then dispatch a job to process.

Focus / keyboard behavior
- Add `wire:keydown.escape="closeX"` to the root element or use Alpine `x-on:keydown.window.escape="..."` to close on Esc.
- Consider adding `tabindex="-1"` to modal panel and using a small script/Alpine to `focus()` the first input.

Examples & snippets
- Use the `Projects` modal in `resources/views/livewire/projects/project-list.blade.php` as the canonical implementation.
- Use the Import modal example above for bulk-import dialogs that require a lighter overlay.

Notes and rationale
- We prefer `bg-base-100` / black icons and `btn` patterns from `DESIGN-UI.md` for a consistent brand.
- The light-blue overlay is reserved for import/bulk actions so it visually signals a different workflow, but still retains accessibility (sufficient contrast for modal panel).

Accessibility checklist (quick)
- [ ] `role="dialog" aria-modal="true"` present
- [ ] Modal has an accessible title (aria-labelledby or aria-label)
- [ ] Backdrop click closes modal
- [ ] Escape key closes modal
- [ ] Focus moves into the modal on open and is trapped
- [ ] Buttons use semantic `<button>` elements (not anchors)

If you want, I can add a small Blade partial `resources/views/components/modal.blade.php` to DRY up modal markup and let all other modal views include it with parameters (width, overlay color, title). Would you like me to add that helper partial now?
