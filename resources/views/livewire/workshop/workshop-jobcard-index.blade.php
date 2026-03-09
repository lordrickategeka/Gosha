<div>
    <x-layouts.dash-layout title="Workshop Jobcards">
        <div class="max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="text-lg font-bold mb-4">Workshop Jobcards</h2>

                <table class="table-auto w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-4 py-2">#</th>
                            <th class="border px-4 py-2">Jobcard ID</th>
                            <th class="border px-4 py-2">Notes</th>
                            <th class="border px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workshopJobcards as $jobcard)
                            <tr>
                                <td class="border px-4 py-2">{{ $jobcard->id }}</td>
                                <td class="border px-4 py-2">{{ $jobcard->jobcard->id ?? 'N/A' }}</td>
                                <td class="border px-4 py-2">{{ $jobcard->notes }}</td>
                                <td class="border px-4 py-2">
                                    <a href="{{ route('workshop-jobcards.show', $jobcard->id) }}" class="text-blue-600">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-layouts.dash-layout>
</div>
