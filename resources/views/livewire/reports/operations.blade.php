<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Operations Report</h1>
            <p class="text-base-content/60">Work orders and wash statistics</p>
        </div>
    </div>

    <x-reports.filters :period="$period" :showYear="true" :showStaff="true" />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Work Orders -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Work Orders</h2>
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center p-4 bg-base-200 rounded-lg">
                        <p class="text-2xl font-bold">{{ $this->workOrderStats['total'] }}</p>
                        <p class="text-xs text-base-content/60">Total</p>
                    </div>
                    <div class="text-center p-4 bg-success/10 rounded-lg">
                        <p class="text-2xl font-bold text-success">{{ $this->workOrderStats['completed'] }}</p>
                        <p class="text-xs text-base-content/60">Completed</p>
                    </div>
                    <div class="text-center p-4 bg-warning/10 rounded-lg">
                        <p class="text-2xl font-bold text-warning">{{ $this->workOrderStats['in_progress'] }}</p>
                        <p class="text-xs text-base-content/60">In Progress</p>
                    </div>
                </div>
                <h3 class="font-medium mb-2">By Type</h3>
                <div class="space-y-2">
                    @foreach($this->workOrderStats['by_type'] as $type => $count)
                        <div class="flex justify-between items-center">
                            <span>{{ ucfirst($type) }}</span>
                            <span class="badge badge-ghost">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Wash Orders -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Wash Orders</h2>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="text-center p-4 bg-base-200 rounded-lg">
                        <p class="text-2xl font-bold">{{ $this->washOrderStats['total'] }}</p>
                        <p class="text-xs text-base-content/60">Total</p>
                    </div>
                    <div class="text-center p-4 bg-success/10 rounded-lg">
                        <p class="text-2xl font-bold text-success">{{ $this->washOrderStats['completed'] }}</p>
                        <p class="text-xs text-base-content/60">Completed</p>
                    </div>
                </div>
                <h3 class="font-medium mb-2">By Source</h3>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span>Walk-in</span>
                        <span class="badge badge-ghost">{{ $this->washOrderStats['walk_in'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Combo (from service)</span>
                        <span class="badge badge-accent">{{ $this->washOrderStats['combo'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm mt-6">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Daily Activity</h2>
            @if($this->dailyVolume->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Date</th><th class="text-right">Work Orders</th><th class="text-right">Wash Orders</th></tr>
                        </thead>
                        <tbody>
                            @foreach($this->dailyVolume as $day)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($day['date'])->format('D, d M') }}</td>
                                    <td class="text-right">{{ $day['work_orders'] }}</td>
                                    <td class="text-right">{{ $day['wash_orders'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center py-8 text-base-content/50">No operations data for selected filters</p>
            @endif
        </div>
    </div>
</div>
