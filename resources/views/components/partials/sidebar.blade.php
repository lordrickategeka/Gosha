<!-- Sidebar -->
<style>
    .app-sidebar details > summary::after,
    .app-sidebar details > summary::before,
    .app-sidebar details > summary::marker {
        display: none !important;
        content: none !important;
    }
    .app-sidebar details[open] > summary .nav-chevron {
        transform: rotate(90deg);
    }
    .nav-chevron {
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }
</style>
<div class="drawer-side z-40">
    <label for="sidebar-drawer" class="drawer-overlay"></label>
    <aside class="app-sidebar w-72 min-h-screen flex flex-col">
<!-- Logo -->
        <div class="border-b border-base-300/80 px-5 py-5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-base-300 bg-neutral text-neutral-content">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <span class="text-lg font-semibold tracking-[-0.03em]">GarageHQ</span>
                    @auth
                    @if(auth()->user()->vendor)
                        <p class="mt-0.5 text-xs uppercase tracking-[0.14em] text-base-content/45">{{ auth()->user()->vendor->name }}</p>
                    @endif
                    @endauth
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-4 py-5 scrollbar-thin">
            <ul class="menu menu-md gap-1">
                <!-- Dashboard -->
                @can('view_dashboard')
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>
                </li>
                @endcan

                <!-- Operations Section -->
                <li class="menu-title mt-4">
                    <span>Operations</span>
                </li>

                <!-- Work Orders -->
                @canany(['view_work_orders', 'view_assigned_work_orders'])
                <li>
                    <details {{ request()->routeIs('work-orders.*') ? 'open' : '' }}>
                        <summary class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-chevron h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Work Orders
                        </summary>
                        <ul>
                            <li><a href="{{ route('work-orders.index') }}" class="{{ request()->routeIs('work-orders.index') ? 'active' : '' }}">All Orders</a></li>
                            @can('create_work_orders')
                            <li><a href="{{ route('work-orders.create') }}" class="{{ request()->routeIs('work-orders.create') ? 'active' : '' }}">New Order</a></li>
                            @endcan
                        </ul>
                    </details>
                </li>
                @endcanany

                <!-- Wash Bay -->
                @canany(['view_wash_orders', 'view_assigned_wash_orders'])
                <li>
                    <details {{ request()->routeIs('wash-orders.*') ? 'open' : '' }}>
                        <summary class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-chevron h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            Wash Bay
                        </summary>
                        <ul>
                            <li><a href="{{ route('wash-orders.index') }}" class="{{ request()->routeIs('wash-orders.index') ? 'active' : '' }}">Queue</a></li>
                            @can('create_wash_orders')
                            <li><a href="{{ route('wash-orders.create') }}" class="{{ request()->routeIs('wash-orders.create') ? 'active' : '' }}">New Wash</a></li>
                            @endcan
                        </ul>
                    </details>
                </li>
                @endcanany

                <!-- Appointments -->
                @can('view_appointments')
                <li>
                    <a href="{{ route('appointments.index') }}" class="{{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Appointments
                    </a>
                </li>
                @endcan

                <!-- Calendar -->
                <li>
                    <a href="{{ route('calendar') }}" class="{{ request()->routeIs('calendar') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar
                    </a>
                </li>

                <!-- Bay Status -->
                @canany(['view_service_bays', 'view_wash_bays'])
                <li>
                    <a href="{{ route('bays.status') }}" class="{{ request()->routeIs('bays.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        Bay Status
                    </a>
                </li>
                @endcanany

                <!-- Customers Section -->
                <li class="menu-title mt-4">
                    <span>Customers</span>
                </li>

                @can('view_customers')
                <li>
                    <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Customers
                    </a>
                </li>
                @endcan

                @can('view_vehicles')
                <li>
                    <a href="{{ route('vehicles.index') }}" class="{{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 4h8m-6 4h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                        </svg>
                        Vehicles
                    </a>
                </li>
                @endcan

                <!-- Finance Section -->
                <li class="menu-title mt-4">
                    <span>Finance</span>
                </li>

                @can('view_invoices')
                <li>
                    <details {{ request()->routeIs('invoices.*') ? 'open' : '' }}>
                        <summary class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-chevron h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Invoices
                        </summary>
                        <ul>
                            <li><a href="{{ route('invoices.index') }}">All Invoices</a></li>
                            <li><a href="{{ route('invoices.index', ['status' => 'unpaid']) }}">Unpaid</a></li>
                            @can('create_invoices')
                            <li><a href="{{ route('invoices.create') }}">New Invoice</a></li>
                            @endcan
                        </ul>
                    </details>
                </li>
                @endcan

                @can('view_quotations')
                <li>
                    <a href="{{ route('quotations.index') }}" class="{{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Quotations
                    </a>
                </li>
                @endcan

                @can('view_payments')
                <li>
                    <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Payments
                    </a>
                </li>
                @endcan

                @can('view_expenses')
                <li>
                    <a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Expenses
                    </a>
                </li>
                @endcan

                @canany(['view_commissions', 'view_own_commissions'])
                <li>
                    <a href="{{ route('commissions.index') }}" class="{{ request()->routeIs('commissions.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2v16z" />
                        </svg>
                        Commissions
                    </a>
                </li>
                @endcanany

<!-- Marketplace Section -->
                @canany(['browse_marketplace', 'view_quotes', 'view_listings', 'view_rfqs', 'view_purchase_orders'])
                <li class="menu-title mt-4">
                    <span>Marketplace</span>
                </li>

                <!-- Supplier Side -->
                @canany(['view_quotes', 'view_listings', 'view_purchase_orders'])
                <li>
                    <details {{ request()->routeIs('supplier.*') ? 'open' : '' }}>
                        <summary class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-chevron h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                            </svg>
                            My Shop
                        </summary>
                        <ul>
                            @can('view_quotes')
                            <li><a href="{{ route('supplier.quotes.inbox') }}" class="{{ request()->routeIs('supplier.quotes.inbox') ? 'active' : '' }}">Quote Inbox</a></li>
                            @endcan
                            @can('view_listings')
                            <li><a href="{{ route('supplier.listings.index') }}" class="{{ request()->routeIs('supplier.listings.*') ? 'active' : '' }}">Listings</a></li>
                            @endcan
                            @can('view_purchase_orders')
                            <li><a href="{{ route('supplier.orders.index') }}" class="{{ request()->routeIs('supplier.orders.*') ? 'active' : '' }}">Orders</a></li>
                            @endcan
                        </ul>
                    </details>
                </li>
                @endcanany

                <!-- Buyer / Browse Side -->
                @can('browse_marketplace')
                <li>
                    <details {{ request()->routeIs('marketplace.*') ? 'open' : '' }}>
                        <summary class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-chevron h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                            Browse
                        </summary>
                        <ul>
                            <li><a href="{{ route('marketplace.browse') }}" class="{{ request()->routeIs('marketplace.browse') ? 'active' : '' }}">All Listings</a></li>
                            @can('view_rfqs')
                            <li><a href="{{ route('marketplace.rfqs.index') }}" class="{{ request()->routeIs('marketplace.rfqs.*') ? 'active' : '' }}">RFQs</a></li>
                            @endcan
                            @can('view_purchase_orders')
                            <li><a href="{{ route('marketplace.purchase-orders.index') }}" class="{{ request()->routeIs('marketplace.purchase-orders.*') ? 'active' : '' }}">Purchase Orders</a></li>
                            @endcan
                        </ul>
                    </details>
                </li>
                @endcan
                @endcanany

                <!-- Inventory Section -->
                @can('view_inventory')
                <li class="menu-title mt-4">
                    <span>Inventory</span>
                </li>

                <li>
                    <details {{ request()->routeIs('inventory.*') ? 'open' : '' }}>
                        <summary class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-chevron h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Stock
                        </summary>
                        <ul>
                            <li><a href="{{ route('inventory.index') }}">All Items</a></li>
                            <li><a href="{{ route('inventory.low-stock') }}">Low Stock</a></li>
                            @can('adjust_stock')
                            <li><a href="{{ route('inventory.movements') }}">Movements</a></li>
                            @endcan
                        </ul>
                    </details>
                </li>

                @can('view_suppliers')
                <li>
                    <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                        </svg>
                        Suppliers
                    </a>
                </li>
                @endcan
                @endcan

                <!-- Reports Section -->
                @can('view_reports')
                <li class="menu-title mt-4">
                    <span>Reports</span>
                </li>

                <li>
                    <details {{ request()->routeIs('reports.*') ? 'open' : '' }}>
                        <summary class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-chevron h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Reports
                        </summary>
                        <ul>
                            <li><a href="{{ route('reports.sales') }}">Sales</a></li>
                            <li><a href="{{ route('reports.operations') }}">Operations</a></li>
                            <li><a href="{{ route('reports.inventory') }}">Inventory</a></li>
                            <li><a href="{{ route('reports.staff') }}">Staff Performance</a></li>
                            @can('view_debtors')
                            <li><a href="{{ route('reports.debtors') }}">Debtors</a></li>
                            @endcan
                            @can('view_creditors')
                            <li><a href="{{ route('reports.creditors') }}">Creditors</a></li>
                            @endcan
                        </ul>
                    </details>
                </li>
                @endcan

                <!-- Settings Section (for managers) -->
                @canany(['view_users', 'view_settings', 'view_service_templates', 'manage_roles'])
                <li class="menu-title mt-4">
                    <span>Settings</span>
                </li>

                @can('view_users')
                <li>
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Staff
                    </a>
                </li>
                @endcan

                @can('manage_roles')
                <li>
                    <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Roles & Permissions
                    </a>
                </li>
                @endcan

                @can('view_branches')
                <li>
                    <a href="{{ route('branches.index') }}" class="{{ request()->routeIs('branches.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Branches
                    </a>
                </li>
                @endcan

                @can('view_service_templates')
                <li>
                    <a href="{{ route('templates.index') }}" class="{{ request()->routeIs('templates.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        Templates
                    </a>
                </li>

                <li>
                    <a href="{{ route('service-types.index') }}" class="{{ request()->routeIs('service-types.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Service Types
                    </a>
                </li>

                <li>
                    <a href="{{ route('service-categories.index') }}" class="{{ request()->routeIs('service-categories.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                        Service Categories
                    </a>
                </li>
                @endcan

                <li>
                    <a href="{{ route('packages.index') }}" class="{{ request()->routeIs('packages.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        Packages
                    </a>
                </li>

                @can('view_settings')
                <li>
                    <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Settings
                    </a>
                </li>
                @endcan
                @endcanany

<!-- Platform Admin Section -->
                @auth
                @if(auth()->user()->isPlatformUser())
                <li class="menu-title mt-4">
                    <span>Platform</span>
                </li>

                <li>
                    <a href="{{ route('platform.vendors.index') }}" class="{{ request()->routeIs('platform.vendors.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Vendors
                    </a>
                </li>

                <li>
                    <a href="{{ route('platform.billing') }}" class="{{ request()->routeIs('platform.billing') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Billing
                    </a>
                </li>

                <li>
                    <a href="{{ route('platform.integrations.index') }}" class="{{ request()->routeIs('platform.integrations.*') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                        </svg>
                        Integrations
                    </a>
                </li>
                @endif
                @endauth
            </ul>
        </nav>

        <!-- Footer -->
        <div class="p-4 border-t border-base-300">
            <div class="text-xs text-base-content/50 text-center">
                GarageHQ v1.0.0
            </div>
        </div>
    </aside>
</div>
