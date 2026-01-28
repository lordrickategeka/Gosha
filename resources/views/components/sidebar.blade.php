<aside class="w-64 bg-base-100 border-r border-gray-200 min-h-screen">
    <nav class="p-6">
        <ul class="menu space-y-2">
            <li>
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 {{ request()->routeIs('dashboard') ? 'bg-primary text-white' : 'text-black hover:bg-gray-100' }} rounded-lg">
                    <i class="fas fa-tachometer-alt w-5 h-5 text-black {{ request()->routeIs('dashboard') ? 'text-white' : '' }}"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('job-cards.index') }}" 
                   class="flex items-center gap-3 {{ request()->routeIs('job-cards.*') ? 'bg-primary text-white' : 'text-black hover:bg-gray-100' }} rounded-lg">
                    <i class="fa-solid fa-sim-card w-5 h-5 text-black {{ request()->routeIs('job-cards.*') ? 'text-white' : '' }}"></i>
                    <span>Job Cards</span>
                </a>
            </li>
            <li>
                <a href="{{ route('vehicle-types') }}" 
                   class="flex items-center gap-3 {{ request()->routeIs('vehicle-types') ? 'bg-primary text-white' : 'text-black hover:bg-gray-100' }} rounded-lg">
                    <i class="fas fa-car w-5 h-5 text-black {{ request()->routeIs('vehicle-types') ? 'text-white' : '' }}"></i>
                    <span>Vehicle Types</span>
                </a>
            </li>
            <li>
                <a href="{{ route('service-types') }}" 
                   class="flex items-center gap-3 {{ request()->routeIs('service-types') ? 'bg-primary text-white' : 'text-black hover:bg-gray-100' }} rounded-lg">
                    <i class="fas fa-broom w-5 h-5 text-black {{ request()->routeIs('service-types') ? 'text-white' : '' }}"></i>
                    <span>Services</span>
                </a>
            </li>
            <li>
                <a href="{{ route('staff.index') }}" 
                   class="flex items-center gap-3 {{ request()->routeIs('staff.*') ? 'bg-primary text-white' : 'text-black hover:bg-gray-100' }} rounded-lg">
                    <i class="fas fa-user-tie w-5 h-5 text-black {{ request()->routeIs('staff.*') ? 'text-white' : '' }}"></i>
                    <span>Staff</span>
                </a>
            </li>
            <li>
                <a href="{{ route('customers') }}" 
                   class="flex items-center gap-3 {{ request()->routeIs('customers') ? 'bg-primary text-white' : 'text-black hover:bg-gray-100' }} rounded-lg">
                    <i class="fas fa-users w-5 h-5 text-black {{ request()->routeIs('customers') ? 'text-white' : '' }}"></i>
                    <span>Customers</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center gap-3 text-black hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-calendar-check w-5 h-5 text-black"></i>
                    <span>Appointments</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center gap-3 text-black hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-tools w-5 h-5 text-black"></i>
                    <span>Work Orders</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center gap-3 text-black hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-warehouse w-5 h-5 text-black"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center gap-3 text-black hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-receipt w-5 h-5 text-black"></i>
                    <span>Invoicing</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center gap-3 text-black hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-chart-line w-5 h-5 text-black"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center gap-3 text-black hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-cog w-5 h-5 text-black"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
