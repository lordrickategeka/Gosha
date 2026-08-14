<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Branches</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Manage your garage locations</p>
        </div>
        @can('create_branches')
            <button wire:click="$set('showCreateModal', true)" class="gh-btn gh-btn--primary">+ Add branch</button>
        @endcan
    </div>

    <div class="gh-grid-3">
        @foreach($this->branches as $branch)
            <div class="gh-card gh-card--pad" style="{{ session('current_branch_id') == $branch->id ? 'border-color:var(--gh-primary); border-width:2px;' : '' }}">
                <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                    <div>
                        <div class="gh-card__title">{{ $branch->name }}</div>
                        @if($branch->address)
                            <p class="gh-muted" style="font-size:11.5px; margin-top:2px;">{{ $branch->address }}</p>
                        @endif
                    </div>
                    @if(session('current_branch_id') == $branch->id)
                        <span class="gh-badge gh-badge--primary">Current</span>
                    @endif
                </div>

                <div class="gh-grid-3" style="margin-top:14px;">
                    <div style="text-align:center; padding:10px; background:var(--gh-base-200); border-radius:var(--gh-radius);">
                        <p style="font-size:16px; font-weight:800;">{{ $branch->work_orders_count }}</p>
                        <p class="gh-muted" style="font-size:10.5px;">Work orders</p>
                    </div>
                    <div style="text-align:center; padding:10px; background:var(--gh-base-200); border-radius:var(--gh-radius);">
                        <p style="font-size:16px; font-weight:800;">{{ $branch->wash_orders_count }}</p>
                        <p class="gh-muted" style="font-size:10.5px;">Washes</p>
                    </div>
                    <div style="text-align:center; padding:10px; background:var(--gh-base-200); border-radius:var(--gh-radius);">
                        <p style="font-size:16px; font-weight:800;">{{ $branch->users_count }}</p>
                        <p class="gh-muted" style="font-size:10.5px;">Staff</p>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:14px;">
                    @if(session('current_branch_id') != $branch->id)
                        <button wire:click="switchBranch({{ $branch->id }})" class="gh-btn gh-btn--primary gh-btn--sm">Switch</button>
                    @endif
                    @can('edit_branches')
                        <a href="{{ route('branches.edit', $branch) }}" class="gh-btn gh-btn--sm">Edit</a>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:16px;">Add Branch</div>
                <form wire:submit="createBranch">
                    <div class="gh-field" style="margin-bottom:12px;">
                        <span class="gh-label">Branch name *</span>
                        <input type="text" wire:model="name" class="gh-input" style="width:100%;" placeholder="Main Branch">
                        @error('name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field" style="margin-bottom:12px;">
                        <span class="gh-label">Address</span>
                        <input type="text" wire:model="address" class="gh-input" style="width:100%;" placeholder="123 Main Street">
                    </div>
                    <div class="gh-field" style="margin-bottom:12px;">
                        <span class="gh-label">Phone</span>
                        <input type="text" wire:model="phone" class="gh-input" style="width:100%;" placeholder="+256 700 000000">
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="gh-btn">Cancel</button>
                        <button type="submit" class="gh-btn gh-btn--primary">Create branch</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showCreateModal', false)"></div>
        </div>
    @endif
</div>
