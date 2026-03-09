<div>
    <x-layouts.dash-layout title="dashboard">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 w-full">
                <h2 class="text-lg font-bold mb-4">Dashboard</h2>
                <p class="text-gray-600">Welcome to your dashboard! Here you can get a quick overview of your recent activity, manage your job cards, and access important metrics.</p>
            </div>
        </div>
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Revenue Card -->
        <div class="card bg-base-100 shadow-md rounded-lg border border-gray-200">
            <div class="card-body p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Monthly Revenue</p>
                        <h3 class="text-3xl font-bold text-black mt-1">${{ number_format($monthlyRevenue, 2) }}</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <span class="text-green-600">+12.5%</span> from last month
                        </p>
                    </div>
                    <i class="fas fa-dollar-sign w-8 h-8 text-black"></i>
                </div>
            </div>
        </div>
        <!-- Active Jobs Card -->
        <div class="card bg-base-100 shadow-md rounded-lg border border-gray-200">
            <div class="card-body p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Active Jobs</p>
                        <h3 class="text-3xl font-bold text-black mt-1">{{ $activeJobs }}</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <span class="text-black">8</span> pending approval
                        </p>
                    </div>
                    <i class="fas fa-clipboard-list w-8 h-8 text-black"></i>
                </div>
            </div>
        </div>
        <!-- Completed Jobs Card -->
        <div class="card bg-base-100 shadow-md rounded-lg border border-gray-200">
            <div class="card-body p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Completed Jobs</p>
                        <h3 class="text-3xl font-bold text-black mt-1">{{ $completedJobs }}</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <span class="text-green-600">+8.2%</span> this month
                        </p>
                    </div>
                    <i class="fas fa-check-circle w-8 h-8 text-black"></i>
                </div>
            </div>
        </div>
        <!-- Customer Satisfaction Card -->
        <div class="card bg-base-100 shadow-md rounded-lg border border-gray-200">
            <div class="card-body p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Customer Rating</p>
                        <h3 class="text-3xl font-bold text-black mt-1">{{ $customerRating }}/5</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Based on 89 reviews
                        </p>
                    </div>
                    <i class="fas fa-star w-8 h-8 text-black"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Revenue Chart -->
        {{-- <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Revenue Trend</h3>
                    <select
                        class="text-sm border border-gray-300 rounded px-3 py-1 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <option>Last 6 months</option>
                        <option>Last year</option>
                    </select>
                </div>
                <canvas id="revenueChart" height="200"></canvas>
            </div> --}}
        <!-- Job Status Chart -->
        {{-- <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Job Status Distribution</h3>
                    <div class="text-sm text-gray-500">This month</div>
                </div>
                <canvas id="jobStatusChart" height="200"></canvas>
            </div> --}}
    </div>

    <!-- Recent Activity & Upcoming Appointments -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Work Orders -->
        <div class="card bg-base-100 shadow-md rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-black">Recent Work Orders</h3>
                    <a href="#" class="text-sm text-primary hover:text-primary-dark">View all</a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <!-- Work Order Item -->
                    <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="fas fa-car text-black text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-black">Oil Change - Honda Civic</p>
                            <p class="text-sm text-gray-500">Sarah Johnson • #WO-2024-0156</p>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-success badge-sm">
                                Completed
                            </span>
                            <p class="text-xs text-gray-500 mt-1">2 hours ago</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="fas fa-tools text-black text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-black">Brake Repair - Ford F-150</p>
                            <p class="text-sm text-gray-500">Mike Davis • #WO-2024-0157</p>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-warning badge-sm">
                                In Progress
                            </span>
                            <p class="text-xs text-gray-500 mt-1">Started 1h ago</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="fas fa-cog text-black text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-black">Engine Diagnostic - BMW X5</p>
                            <p class="text-sm text-gray-500">Emily Rodriguez • #WO-2024-0158</p>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-ghost badge-sm">
                                Pending
                            </span>
                            <p class="text-xs text-gray-500 mt-1">Scheduled</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Upcoming Appointments -->
        <div class="card bg-base-100 shadow-md rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-black">Today's Appointments</h3>
                    <a href="#" class="text-sm text-primary hover:text-primary-dark">View calendar</a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <!-- Appointment Item -->
                    <div class="flex items-center space-x-4 p-3 border-l-4 border-primary bg-gray-50 rounded-r-lg">
                        <div class="text-center">
                            <p class="text-lg font-bold text-black">09:00</p>
                            <p class="text-xs text-gray-500">AM</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-black">Annual Inspection</p>
                            <p class="text-sm text-gray-500">Toyota Camry - License: ABC123</p>
                            <p class="text-xs text-gray-500">Customer: John Williams</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500">Bay 2</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 p-3 border-l-4 border-secondary bg-gray-50 rounded-r-lg">
                        <div class="text-center">
                            <p class="text-lg font-bold text-black">11:30</p>
                            <p class="text-xs text-gray-500">AM</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-black">Tire Rotation</p>
                            <p class="text-sm text-gray-500">Nissan Altima - License: XYZ789</p>
                            <p class="text-xs text-gray-500">Customer: Lisa Chen</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500">Bay 1</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 p-3 border-l-4 border-gray-300 bg-gray-50 rounded-r-lg">
                        <div class="text-center">
                            <p class="text-lg font-bold text-black">02:00</p>
                            <p class="text-xs text-gray-500">PM</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-black">AC Repair</p>
                            <p class="text-sm text-gray-500">Chevrolet Malibu - License: DEF456</p>
                            <p class="text-xs text-gray-500">Customer: Robert Martinez</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500">Bay 3</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 p-3 border-l-4 border-gray-200 bg-gray-50 rounded-r-lg">
                        <div class="text-center">
                            <p class="text-lg font-bold text-black">04:30</p>
                            <p class="text-xs text-gray-500">PM</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-black">Transmission Service</p>
                            <p class="text-sm text-gray-500">Ford Explorer - License: GHI789</p>
                            <p class="text-xs text-gray-500">Customer: Amanda Taylor</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500">Bay 4</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [15000, 16500, 14200, 17800, 19200, 18742],
                        borderColor: '#374151',
                        backgroundColor: 'rgba(55, 65, 81, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f3f4f6'
                            },
                            ticks: {
                                color: '#6b7280',
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6b7280'
                            }
                        }
                    }
                }
            });

            // Job Status Chart
            const jobStatusCtx = document.getElementById('jobStatusChart').getContext('2d');
            new Chart(jobStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'In Progress', 'Pending', 'On Hold'],
                    datasets: [{
                        data: [124, 47, 23, 8],
                        backgroundColor: [
                            '#374151',
                            '#6b7280',
                            '#9ca3af',
                            '#d1d5db'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                color: '#6b7280',
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        </script>
    @endpush
    <!-- ...existing dashboard content... -->
    </x-layouts.dash-layout>

</div>
