<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Staff Performance</h1>
            <p class="text-base-content/60">Technician and attendant productivity</p>
        </div>
    </div>

    <x-reports.filters :period="$period" :showYear="true" :showStaff="true" />

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Technicians</div>
            <div class="stat-value text-lg">{{ number_format($this->summary['technicians']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Attendants</div>
            <div class="stat-value text-lg">{{ number_format($this->summary['attendants']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Completed Orders</div>
            <div class="stat-value text-lg text-success">{{ number_format($this->summary['completed_orders']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Completed Washes</div>
            <div class="stat-value text-lg text-info">{{ number_format($this->summary['completed_washes']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Commission</div>
            <div class="stat-value text-lg">UGX {{ number_format($this->summary['commission_total']) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Technicians -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    </svg>
                    Technicians
                </h2>
                @if($this->technicianStats->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr><th>Name</th><th class="text-right">Completed</th><th class="text-right">Commission</th></tr>
                            </thead>
                            <tbody>
                                @foreach($this->technicianStats as $tech)
                                    <tr class="hover">
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="avatar placeholder">
                                                    <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                        <span class="text-xs">{{ substr($tech->name, 0, 1) }}</span>
                                                    </div>
                                                </div>
                                                <a href="{{ route('users.show', $tech) }}" class="link link-hover font-medium">{{ $tech->name }}</a>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-success">{{ $tech->completed_orders }}</span>
                                        </td>
                                        <td class="text-right font-medium">UGX {{ number_format($tech->total_commission ?? 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center py-8 text-base-content/50">No technicians found</p>
                @endif
            </div>
        </div>

        <!-- Wash Attendants -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    Wash Attendants
                </h2>
                @if($this->washAttendantStats->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr><th>Name</th><th class="text-right">Completed</th><th class="text-right">Commission</th></tr>
                            </thead>
                            <tbody>
                                @foreach($this->washAttendantStats as $attendant)
                                    <tr class="hover">
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="avatar placeholder">
                                                    <div class="bg-info text-info-content rounded-full w-8">
                                                        <span class="text-xs">{{ substr($attendant->name, 0, 1) }}</span>
                                                    </div>
                                                </div>
                                                <a href="{{ route('users.show', $attendant) }}" class="link link-hover font-medium">{{ $attendant->name }}</a>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-info">{{ $attendant->completed_washes }}</span>
                                        </td>
                                        <td class="text-right font-medium">UGX {{ number_format($attendant->total_commission ?? 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center py-8 text-base-content/50">No wash attendants found</p>
                @endif
            </div>
        </div>
    </div>
</div>
