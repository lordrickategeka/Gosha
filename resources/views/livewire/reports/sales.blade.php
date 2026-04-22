<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Sales Report</h1>
            <p class="text-base-content/60">Revenue and payment analytics</p>
        </div>
    </div>

    <x-reports.filters :period="$period" :showYear="true" :showStaff="true" />

    <x-reports.export-controls />

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Total Invoiced</div>
            <div class="stat-value text-lg">UGX {{ number_format($this->stats['total_invoiced']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Collected</div>
            <div class="stat-value text-lg text-success">UGX {{ number_format($this->stats['total_collected']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Pending</div>
            <div class="stat-value text-lg text-warning">UGX {{ number_format($this->stats['pending']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Invoices</div>
            <div class="stat-value text-lg">{{ $this->stats['invoice_count'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Avg Invoice</div>
            <div class="stat-value text-lg">UGX {{ number_format($this->stats['avg_invoice']) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Revenue -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Daily Revenue</h2>
                @if($this->dailyRevenue->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead><tr><th>Date</th><th class="text-right">Revenue</th></tr></thead>
                            <tbody>
                                @foreach($this->dailyRevenue as $day)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($day->date)->format('D, d M') }}</td>
                                        <td class="text-right font-medium">UGX {{ number_format($day->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center py-8 text-base-content/50">No data for selected period</p>
                @endif
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Payment Methods</h2>
                @if($this->paymentMethods->count() > 0)
                    <div class="space-y-4">
                        @foreach($this->paymentMethods as $method)
                            @php
                                $percentage = $this->stats['total_collected'] > 0 ? ($method->total / $this->stats['total_collected']) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $method->payment_method)) }}</span>
                                    <span>UGX {{ number_format($method->total) }} ({{ $method->count }})</span>
                                </div>
                                <progress class="progress progress-primary w-full" value="{{ $percentage }}" max="100"></progress>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center py-8 text-base-content/50">No payments for selected period</p>
                @endif
            </div>
        </div>
    </div>
</div>
