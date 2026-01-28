@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Job Card #{{ $jobCard->job_card_number }}</h1>
                    <p class="mt-2 text-sm text-gray-700">Created on {{ $jobCard->created_at->format('M d, Y \a\t H:i') }}</p>
                </div>
                <div class="flex space-x-2">
                    @if($jobCard->status !== 'collected')
                        <button onclick="updateStatus({{ $jobCard->id }}, '{{ $jobCard->status }}')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Update Status
                        </button>
                    @endif
                    @if($jobCard->invoice && $jobCard->invoice->status !== 'paid')
                        <a href="{{ route('payments.create', $jobCard->invoice) }}"
                           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Process Payment
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Job Card Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Job Details</h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vehicle</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $jobCard->number_plate }} ({{ $jobCard->vehicleType->name }})</dd>
                            </div>
                            <!-- Service Type removed -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Assigned Staff</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $jobCard->staff->name }} ({{ ucfirst($jobCard->staff->role) }})</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Customer Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $jobCard->phone }}</dd>
                            </div>
                            @if($jobCard->customer_name)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Customer Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $jobCard->customer_name }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $jobCard->status === 'completed' ? 'bg-green-100 text-green-800' :
                                           ($jobCard->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                           ($jobCard->status === 'collected' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $jobCard->status)) }}
                                    </span>
                                </dd>
                            </div>
                        </dl>

                        @if($jobCard->notes)
                            <div class="mt-6">
                                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $jobCard->notes }}</dd>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Service Timeline</h2>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li>
                                    <div class="relative pb-8">
                                        <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5z"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">Vehicle received</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    {{ ($jobCard->intake_datetime ?? $jobCard->created_at)->format('M d, Y H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                @if($jobCard->started_at)
                                    <li>
                                        <div class="relative pb-8">
                                            <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 2L3 7v11a2 2 0 002 2h10a2 2 0 002-2V7l-7-5z"></path>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">Service started</p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        {{ $jobCard->started_at->format('M d, Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                @if($jobCard->completed_at)
                                    <li>
                                        <div class="relative pb-8">
                                            <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">Service completed</p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        {{ $jobCard->completed_at->format('M d, Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                @if($jobCard->collected_at)
                                    <li>
                                        <div class="relative">
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">Vehicle collected</p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        {{ $jobCard->collected_at->format('M d, Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Invoice Summary -->
                @if($jobCard->invoice)
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Invoice</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Invoice Number:</span>
                                    <span class="font-medium">{{ $jobCard->invoice->invoice_number }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Amount:</span>
                                    <span class="font-medium">UGX {{ number_format($jobCard->invoice->total_amount) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Paid:</span>
                                    <span class="font-medium">UGX {{ number_format($jobCard->invoice->total_paid) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Balance:</span>
                                    <span class="font-medium {{ $jobCard->invoice->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        UGX {{ number_format($jobCard->invoice->balance) }}
                                    </span>
                                </div>
                                <div class="pt-3 border-t">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $jobCard->invoice->status === 'paid' ? 'bg-green-100 text-green-800' :
                                           ($jobCard->invoice->status === 'partially_paid' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst(str_replace('_', ' ', $jobCard->invoice->status)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Payment History -->
                @if($jobCard->invoice && $jobCard->invoice->payments->count() > 0)
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Payment History</h3>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($jobCard->invoice->payments as $payment)
                                <div class="p-6">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                UGX {{ number_format($payment->amount_paid) }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                            </p>
                                            @if($payment->receipt)
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Receipt: {{ $payment->receipt->receipt_number }}
                                                </p>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            {{ $payment->payment_date->format('M d, Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Commission Info -->
                @if($jobCard->commission)
                    <div class="bg-white shadow-sm rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Commission</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Staff:</span>
                                    <span class="font-medium">{{ $jobCard->staff->name }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Commission:</span>
                                    <span class="font-medium text-green-600">UGX {{ number_format($jobCard->commission->commission_amount) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Status:</span>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $jobCard->commission->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($jobCard->commission->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Update Job Status</h3>
            <form id="statusForm" method="POST" action="{{ route('job-cards.update-status', $jobCard) }}">
                @csrf
                @method('PATCH')
                <div class="mt-4">
                    <select id="newStatus" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="pending" {{ $jobCard->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $jobCard->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $jobCard->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="collected" {{ $jobCard->status === 'collected' ? 'selected' : '' }}>Collected</option>
                    </select>
                </div>
                <div class="items-center px-4 py-3">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700">
                        Update Status
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-700">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateStatus(jobCardId, currentStatus) {
    document.getElementById('newStatus').value = currentStatus;
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('statusModal').classList.add('hidden');
}
</script>
@endsection
