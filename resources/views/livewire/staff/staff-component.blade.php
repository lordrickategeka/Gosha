<div>
    <x-layouts.dash-layout title="Staff">
        <h2 class="text-2xl font-bold mb-6">Staff List</h2>

        <div class="card bg-base-100 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 p-4 border-b border-base-200">
                <div>
                    <h2 class="text-lg font-semibold">Staff</h2>
                    <p class="text-sm text-base-content/60">{{ $staffList->total() }} total records</p>
                </div>

                <!-- Table Filters -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Search -->
                    <div class="form-control">
                        <input
                            type="text"
                            wire:model="search"
                            class="input input-bordered input-sm w-44"
                            placeholder="Search staff..."
                        />
                    </div>

                    <!-- Clear Filter -->
                    @if($search)
                    <button wire:click="$set('search', '')" class="btn btn-xs btn-ghost" title="Clear search">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    @endif

                    <!-- Add Staff -->
                    <button wire:click="redirectToCreateStaff" class="btn btn-primary btn-sm">
                        + Add Staff
                    </button>
                </div>
            </div>

            @if (session()->has('message'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                    {{ session('message') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Base Salary</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffList as $staff)
                            <tr class="hover">
                                <td>
                                    <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-base-200 font-bold text-xs">
                                        {{ $loop->iteration }}
                                    </span>
                                </td>
                                <td>
                                    <div class="font-semibold">{{ $staff->name }}</div>
                                    <div class="text-xs text-base-content/60">ID: #ST{{ str_pad($staff->id, 3, '0', STR_PAD_LEFT) }}</div>
                                </td>
                                <td>{{ $staff->phone }}</td>
                                <td>{{ $staff->email }}</td>
                                <td class="capitalize">{{ $staff->role }}</td>
                                <td class="font-mono">UGX {{ number_format($staff->base_salary, 2) }}</td>
                                <td>
                                    @if ($staff->is_active)
                                        <span class="badge badge-success badge-sm">Active</span>
                                    @else
                                        <span class="badge badge-warning badge-sm">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="btn btn-ghost btn-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </label>
                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40">
                                            <li><button wire:click="edit({{ $staff->id }})">Edit</button></li>
                                            <li><button wire:click="confirmDelete({{ $staff->id }})" class="text-error">Delete</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-base-content/50">No staff found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($staffList->hasPages())
                <div class="p-4 border-t border-base-200">
                    {{ $staffList->links() }}
                </div>
            @endif
        </div>

        <!-- Delete Confirmation Modal -->
        @if ($staffId && !$editMode)
            <div class="modal modal-open" role="dialog">
                <div class="modal-box max-w-sm">
                    <h3 class="font-bold text-lg mb-4">Delete Staff</h3>
                    <p class="mb-6">Are you sure you want to delete this staff member?</p>
                    <div class="modal-action">
                        <button wire:click="resetForm" class="btn btn-ghost">Cancel</button>
                        <button wire:click="delete" class="btn btn-error">Delete</button>
                    </div>
                </div>
                <div class="modal-backdrop" wire:click="resetForm"></div>
            </div>
        @endif
    </x-layouts.dash-layout>
</div>
