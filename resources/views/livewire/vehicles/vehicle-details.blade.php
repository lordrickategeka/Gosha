<div>
    <x-layouts.dash-layout title="Vehicle Details">
        <div class="max-w-4xl  p-6 bg-white rounded-lg shadow">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Vehicle Details</h2>

            @if ($vehicle)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Vehicle Information</h3>
                        <ul class="mt-2 text-sm text-gray-600">
                            <li><strong>Vehicle Name:</strong> {{ $vehicle->vehicle_name }}</li>
                            <li><strong>Number Plate:</strong> {{ $vehicle->number_plate }}</li>
                            <li><strong>Created At:</strong> {{ $vehicle->created_at }}</li>
                            <li><strong>Updated At:</strong> {{ $vehicle->updated_at }}</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Customer Information</h3>
                        <ul class="mt-2 text-sm text-gray-600">
                            <li><strong>Customer Name:</strong> {{ $vehicle->customer->customer_name ?? 'N/A' }}</li>
                            <li><strong>Email:</strong> {{ $vehicle->customer->email ?? 'N/A' }}</li>
                            <li><strong>Phone:</strong> {{ $vehicle->customer->phone ?? 'N/A' }}</li>
                        </ul>
                    </div>
                </div>

                @if ($vehicle && $jobCards->isNotEmpty())


                    @if ($vehicle && $jobCards->isNotEmpty())
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Job Card Details</h3>
                            <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
                                <thead>
                                    <tr class="bg-gray-200">
                                        <th class="border px-3 py-2">#</th>
                                        <th class="border px-3 py-2">Job Card Number</th>
                                        <th class="border px-3 py-2">Status</th>
                                        <th class="border px-3 py-2">Started At</th>
                                        <th class="border px-3 py-2">Completed At</th>
                                        <th class="border px-3 py-2">Collected At</th>
                                        <th class="border px-3 py-2">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($jobCards as $jobCard)
                                        <tr class="hover:bg-gray-100">
                                            <td class="border px-3 py-2 text-center">{{ $loop->iteration }}</td>
                                            <td class="border px-3 py-2">{{ $jobCard->job_card_number }}</td>
                                            <td class="border px-3 py-2">{{ $jobCard->status }}</td>
                                            <td class="border px-3 py-2">{{ $jobCard->started_at }}</td>
                                            <td class="border px-3 py-2">{{ $jobCard->completed_at }}</td>
                                            <td class="border px-3 py-2">{{ $jobCard->collected_at }}</td>
                                            <td class="border px-3 py-2">{{ $jobCard->notes }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-gray-500 mt-8">No job cards found for this vehicle.</p>
                    @endif

                    @if ($vehicle && $workshopJobcards->isNotEmpty())
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Workshop Job Cards</h3>
                            <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
                                <thead>
                                    <tr class="bg-gray-200">
                                        <th class="border px-3 py-2">#</th>
                                        <th class="border px-3 py-2">Workshop Job Card Number</th>
                                        <th class="border px-3 py-2">Material Name</th>
                                        <th class="border px-3 py-2">Quantity</th>
                                        <th class="border px-3 py-2">Notes</th>
                                        <th class="border px-3 py-2">Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workshopJobcards as $workshopJobcard)
                                        <tr class="hover:bg-gray-100">
                                            <td class="border px-3 py-2 text-center">{{ $loop->iteration }}</td>
                                            <td class="border px-3 py-2">
                                                {{ $workshopJobcard->workshop_jobcard_number }}</td>
                                            <td class="border px-3 py-2">{{ $workshopJobcard->material_name }}</td>
                                            <td class="border px-3 py-2">{{ $workshopJobcard->quantity }}</td>
                                            <td class="border px-3 py-2">{{ $workshopJobcard->notes }}</td>
                                            <td class="border px-3 py-2">{{ $workshopJobcard->created_at }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-gray-500 mt-8">No workshop job cards found for this vehicle.</p>
                    @endif
                @else
                    <p class="text-center text-gray-500">Vehicle details not found.</p>
                @endif

            @endif

            <div class="mt-6">
                    <a href="{{ route('vehicles.all') }}"
                        class="px-4 py-2 bg-gray-600 text-white rounded shadow hover:bg-gray-700 focus:outline-none text-sm">Back
                        to Vehicles</a>
                </div>
        </div>
    </x-layouts.dash-layout>
</div>
