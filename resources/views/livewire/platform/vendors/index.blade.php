<div class="gh-page">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Vendors</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Manage all registered vendors on the platform</p>
        </div>
        <a href="{{ route('platform.vendors.create') }}" class="gh-btn gh-btn--primary">+ Add vendor</a>
    </div>

    <div class="gh-grid-4">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Total vendors</span>
            <span class="gh-stat__value">{{ $stats['total'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Active</span>
            <span class="gh-stat__value" style="color:var(--gh-success);">{{ $stats['active'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">On trial</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">{{ $stats['trial'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Suspended</span>
            <span class="gh-stat__value" style="color:var(--gh-error);">{{ $stats['suspended'] }}</span>
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div style="display:flex; flex-wrap:wrap; gap:10px;">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or email..." class="gh-input" style="flex:1; min-width:220px;">
            <select wire:model.live="statusFilter" class="gh-select">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="trial">Trial</option>
                <option value="suspended">Suspended</option>
            </select>
            <select wire:model.live="typeFilter" class="gh-select">
                <option value="">All Types</option>
                <option value="garage">Garage</option>
                <option value="car_wash">Car wash</option>
                <option value="supplier">Supplier</option>
                <option value="hybrid">Hybrid</option>
            </select>
            <select wire:model.live="supplierReview" class="gh-select" aria-label="Supplier review status"><option value="">All supplier reviews</option><option value="pending">Pending supplier approval</option><option value="verified">Verified suppliers</option></select>
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Contact</th>
                        <th>Type</th>
                        <th>Branches</th>
                        <th>Users</th>
                        <th>Billing</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div class="gh-sidebar__mark" style="width:36px; height:36px; border-radius:50%; font-size:12px;">{{ strtoupper(substr($vendor->name, 0, 2)) }}</div>
                                    <div>
                                        <a href="{{ route('platform.vendors.show', $vendor) }}" class="is-ref" style="font-weight:700;">{{ $vendor->name }}</a>
                                        <p class="gh-muted" style="font-size:10.5px;">{{ $vendor->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p>{{ $vendor->email }}</p>
                                @if($vendor->phone)
                                    <p class="gh-muted" style="font-size:11px;">{{ $vendor->phone }}</p>
                                @endif
                            </td>
                            <td>
                                <span class="gh-badge">{{ ucfirst(str_replace('_', ' ', $vendor->vendor_type ?? 'garage')) }}</span>
                                @if(in_array($vendor->vendor_type, ['supplier', 'hybrid']))
                                    @if($vendor->is_verified_supplier)
                                        <p class="gh-hint" style="color:var(--gh-success); margin-top:2px;">Verified</p>
                                    @else
                                        <p class="gh-hint" style="color:var(--gh-warning); margin-top:2px;">Pending verification</p>
                                    @endif
                                @endif
                            </td>
                            <td class="gh-muted">{{ $vendor->branches_count }}</td>
                            <td class="gh-muted">{{ $vendor->users_count }}</td>
                            <td>
                                @if($vendor->billingConfig)
                                    <span class="gh-badge">{{ ucfirst(str_replace('_', ' ', $vendor->billingConfig->billing_model)) }}</span>
                                @else
                                    <span class="gh-hint">Not set</span>
                                @endif
                            </td>
                            <td>
                                @if($vendor->status === 'active')
                                    <span class="gh-badge gh-badge--success">Active</span>
                                @elseif($vendor->status === 'trial')
                                    <span class="gh-badge gh-badge--warning">Trial</span>
                                    @if($vendor->trial_ends_at)
                                        <p class="gh-hint" style="margin-top:2px;">{{ $vendor->isTrialExpired() ? 'Expired' : 'Ends ' . $vendor->trial_ends_at->diffForHumans() }}</p>
                                    @endif
                                @elseif($vendor->status === 'suspended')
                                    <span class="gh-badge gh-badge--error">Suspended</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <div class="dropdown dropdown-end">
                                    <button tabindex="0" type="button" class="gh-btn gh-btn--sm">⋯</button>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-44 border border-base-300">
                                        <li><a href="{{ route('platform.vendors.show', $vendor) }}">View details</a></li>
                                        @can('verify_catalog_products')
                                            @if(in_array($vendor->vendor_type, ['supplier', 'hybrid']))
                                                <li>
                                                    <button wire:click="toggleSupplierVerification({{ $vendor->id }})">
                                                        {{ $vendor->is_verified_supplier ? 'Unverify supplier' : 'Verify supplier' }}
                                                    </button>
                                                </li>
                                            @endif
                                        @endcan
                                        <li>
                                            <button wire:click="toggleStatus({{ $vendor->id }})" wire:confirm="Are you sure you want to {{ $vendor->status === 'suspended' ? 'activate' : 'suspend' }} this vendor?">
                                                {{ $vendor->status === 'suspended' ? 'Activate' : 'Suspend' }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No vendors found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vendors->hasPages())
            <div style="padding:12px 16px; border-top:1px solid var(--gh-hairline);">{{ $vendors->links() }}</div>
        @endif
    </div>
</div>
