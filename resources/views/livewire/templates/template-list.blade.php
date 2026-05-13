<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Document Templates</h1>
            <p class="text-base-content/60">Design custom templates for invoices, work orders, and more</p>
        </div>
        <a href="{{ route('templates.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            New Template
        </a>
    </div>

    {{-- Filters --}}
    <div class="flex gap-4 mb-6">
        <div class="form-control flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search templates..."
                class="input input-bordered"
            />
        </div>

        <select wire:model.live="documentType" class="select select-bordered">
            <option value="all">All Types</option>
            <option value="invoice">Invoices</option>
            <option value="work_order">Work Orders</option>
            <option value="quotation">Quotations</option>
            <option value="receipt">Receipts</option>
            <option value="report">Reports</option>
        </select>
    </div>

    {{-- Templates Grid --}}
    @if($this->templates->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
            @foreach($this->templates as $template)
                <div class="card bg-base-100 shadow-sm border border-base-300 hover:shadow-md transition">
                    {{-- Preview Thumbnail --}}
                    <figure class="bg-base-200 h-48 flex items-center justify-center relative">
                        <div class="text-6xl">📄</div>
                        @if(!$template->is_active)
                            <div class="absolute inset-0 bg-base-300/50 flex items-center justify-center">
                                <span class="badge badge-ghost">Inactive</span>
                            </div>
                        @endif
                    </figure>

                    <div class="card-body p-4">
                        {{-- Name & Type --}}
                        <div class="mb-2">
                            <h3 class="font-medium truncate" title="{{ $template->name }}">{{ $template->name }}</h3>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <span class="badge badge-sm">{{ ucfirst(str_replace('_', ' ', $template->document_type)) }}</span>
                                @if($template->is_default)
                                    <span class="badge badge-primary badge-sm">Default</span>
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="card-actions justify-between mt-2">
                            <div class="dropdown">
                                <label tabindex="0" class="btn btn-ghost btn-sm btn-square">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                    </svg>
                                </label>
                                <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52 z-10">
                                    @if(!$template->is_default)
                                        <li>
                                            <button wire:click="setAsDefault({{ $template->id }})">
                                                Set as Default
                                            </button>
                                        </li>
                                    @endif
                                    <li>
                                        <button wire:click="toggleActive({{ $template->id }})">
                                            {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </li>
                                    <li>
                                        <button wire:click="duplicate({{ $template->id }})">
                                            Duplicate
                                        </button>
                                    </li>
                                    <li class="menu-title">
                                        <span>Danger Zone</span>
                                    </li>
                                    <li>
                                        <button
                                            wire:click="delete({{ $template->id }})"
                                            wire:confirm="Are you sure you want to delete this template?"
                                            class="text-error"
                                        >
                                            Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <a href="{{ route('templates.edit', $template) }}" class="btn btn-sm btn-primary">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $this->templates->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="card bg-base-100 shadow-sm border border-base-300">
            <div class="card-body text-center py-12">
                <div class="text-6xl mb-4">📄</div>
                <h3 class="text-xl font-medium mb-2">No templates found</h3>
                <p class="text-base-content/60 mb-4">
                    @if($search || $documentType !== 'all')
                        Try adjusting your filters or create a new template.
                    @else
                        Get started by creating your first document template.
                    @endif
                </p>
                <a href="{{ route('templates.create') }}" class="btn btn-primary">
                    Create Your First Temp late
                </a>
            </div>
        </div>
    @endif
</div>
