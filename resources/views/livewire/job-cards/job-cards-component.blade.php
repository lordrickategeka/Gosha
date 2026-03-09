<div>
    <x-layouts.dash-layout title="Job Cards">
        <div class="max-w-12xl px-4 sm:px-6 lg:px-6">
            <!-- Header -->
            <div class="sm:flex sm:items-center sm:justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Job Cards</h1>
                    <p class="mt-2 text-sm text-gray-700">Manage all vehicle service job cards</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('job-cards.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z">
                            </path>
                        </svg>
                        New Job Card
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow-sm rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('job-cards.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-2 items-end text-sm">
                        <div>
                            <label for="status" class="block text-xs font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 text-sm py-1 px-2">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="collected" {{ request('status') === 'collected' ? 'selected' : '' }}>Collected</option>
                            </select>
                        </div>
                        <div>
                            <label for="date_from" class="block text-xs font-medium text-gray-700">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="mt-1 block w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 text-sm py-1 px-2" />
                        </div>
                        <div>
                            <label for="date_to" class="block text-xs font-medium text-gray-700">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="mt-1 block w-full border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500 text-sm py-1 px-2" />
                        </div>
                        <div class="flex items-center">
                            <button type="submit" class="w-full px-3 py-1 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Job Cards Table -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">#</th>
                                <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Job Card</th>
                                <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Vehicle</th>
                                <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                                <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Service</th>
                                <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Staff</th>
                                <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Created</th>
                                <th class="px-2 py-2 text-left font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($jobCards as $jobCard)
                                @php $rowNumber = ($jobCards->currentPage() - 1) * $jobCards->perPage() + $loop->iteration; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 py-2">
                                        <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-gray-200 text-gray-700 font-bold text-xs">
                                            {{ $rowNumber }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2 font-semibold text-gray-900">
                                        <a href="{{ route('workshop-jobcards.create', $jobCard->id) }}" class="text-blue-600 hover:underline">{{ $jobCard->job_card_number }}</a>
                                    </td>
                                    <td class="px-2 py-2">
                                        <div class="text-gray-900">{{ $jobCard->number_plate }}</div>
                                        <div class="text-[10px] text-gray-500">{{ $jobCard->vehicleType->name }}</div>
                                        @if($jobCard->customer && $jobCard->customer->vehicles && $jobCard->customer->vehicles->count())
                                            <div class="mt-1">
                                                <span class="block text-[10px] text-gray-500 font-semibold">Customer Vehicles:</span>
                                                <ul class="list-disc ml-4 text-[11px] text-gray-700">
                                                    @foreach($jobCard->customer->vehicles as $v)
                                                        <li>
                                                            {{ $v->number_plate }} @if($v->vehicle_name) ({{ $v->vehicle_name }}) @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2">
                                        <div class="text-gray-900 text-xs">
                                            {{ $jobCard->customer && $jobCard->customer->customer_name ? $jobCard->customer->customer_name : ($jobCard->customer_name ?: 'N/A') }}
                                        </div>
                                        <div class="text-[10px] text-gray-500">
                                            {{ $jobCard->customer && $jobCard->customer->phone ? $jobCard->customer->phone : $jobCard->phone }}
                                        </div>
                                    </td>
                                    <td class="px-2 py-2">
                                        @if($jobCard->clientNarrations && $jobCard->clientNarrations->count())
                                            <div class="flex flex-col gap-1">
                                                @foreach($jobCard->clientNarrations as $narr)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-800 text-[10px] font-medium">
                                                        {{ \Illuminate\Support\Str::limit($narr->issue, 80) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2">
                                        <span class="inline-flex items-center h-7 px-2 rounded-full bg-blue-100 text-blue-800 text-[10px] font-medium">
                                            {{ $jobCard->staff->name }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2">
                                        <span class="inline-block px-2 py-1 text-[10px] rounded-full font-semibold
                                            {{ $jobCard->status === 'completed'
                                                ? 'bg-green-100 text-green-800'
                                                : ($jobCard->status === 'in_progress'
                                                    ? 'bg-yellow-100 text-yellow-800'
                                                    : ($jobCard->status === 'collected'
                                                        ? 'bg-blue-100 text-blue-800'
                                                        : 'bg-gray-100 text-gray-800')) }}">
                                            {{ ucfirst(str_replace('_', ' ', $jobCard->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2 text-[10px] text-gray-500">
                                        {{ $jobCard->created_at->format('M d, Y') }}
                                        <div class="text-[9px] text-gray-400">{{ $jobCard->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-2 py-2 text-gray-500 text-xs">
                                        <div class="flex items-center space-x-2">
                                            <a href="#" wire:click.prevent="editJobCard({{ $jobCard->id }})" class="hover:text-blue-600 p-1 rounded transition" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-8l7 7" /></svg>
                                            </a>
                                            <button type="button" onclick="if(!confirm('Delete this job card?')) return;" wire:click.prevent="deleteJobCard({{ $jobCard->id }})" class="hover:text-red-600 p-1 rounded transition" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7m5-4h4m-6 4V4a1 1 0 011-1h4a1 1 0 011 1v3" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-4 text-center text-gray-500">No job cards found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination and rows per page (static example, replace with Livewire pagination if needed) -->
                <div class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex items-center justify-between">
                        <div>
                            <label for="rows" class="mr-2 text-sm text-gray-700">Rows per page:</label>
                            <select id="rows" class="border-gray-300 rounded p-1 text-sm">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                            </select>
                        </div>
                        <div class="ml-4 text-sm text-gray-700">Showing 1-{{ count($jobCards) }} of {{ count($jobCards) }} job cards</div>
                    </div>
                    <div>
                        <nav class="inline-flex -space-x-px" aria-label="Pagination">
                            <a href="#" class="px-2 py-1 rounded-l border border-gray-300 bg-white text-sm text-gray-500 hover:bg-gray-50">Previous</a>
                            <a href="#" class="px-2 py-1 border-t border-b border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50">1</a>
                            <a href="#" class="px-2 py-1 rounded-r border border-gray-300 bg-white text-sm text-gray-500 hover:bg-gray-50">Next</a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Update Modal -->
        <div id="statusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Update Job Status</h3>
                    <form id="statusForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mt-4">
                            <select id="newStatus" name="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="collected">Collected</option>
                            </select>
                        </div>
                        <div class="items-center px-4 py-3">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                Update Status
                            </button>
                            <button type="button" onclick="closeModal()"
                                class="mt-3 px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function updateStatus(jobCardId, currentStatus) {
                document.getElementById('statusForm').action = `/job-cards/${jobCardId}/status`;
                document.getElementById('newStatus').value = currentStatus;
                document.getElementById('statusModal').classList.remove('hidden');
            }

            function closeModal() {
                document.getElementById('statusModal').classList.add('hidden');
            }
        </script>
    </x-layouts.dash-layout>
</div>
