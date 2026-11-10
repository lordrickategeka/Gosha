<div class="gh-page" style="max-width:900px;">
    <section class="gh-card gh-card--pad">
        <p class="gh-muted" style="font-size:11px; text-transform:uppercase; letter-spacing:.1em;">Grow with GarageHQ</p>
        <h1 style="font-size:28px; font-weight:700; margin:8px 0;">Your garage can sell parts too.</h1>
        <p class="gh-muted">Add the supplier module to {{ $vendor->name }} using your existing account. Keep your garage workspace, records, branches and staff.</p>
    </section>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if($vendor->isVerifiedSupplier())
        <section class="gh-card gh-card--pad">
            <h2 class="gh-card__title">Supplier business approved</h2>
            @if(auth()->user()->hasAnyRole(['supplier_admin', 'supplier_staff']))
                <p>You can switch between garage work and supplier sales from Marketplace.</p>
                <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:16px;"><a href="{{ route('supplier.dashboard') }}" class="gh-btn gh-btn--primary">Open supplier workspace</a><a href="{{ route('supplier.catalog.index') }}" class="gh-btn">Browse catalog</a></div>
            @elseif($canRequest)
                <p>Your business is already verified. Activate supplier workspace access for your owner login.</p><button type="button" wire:click="activate" wire:loading.attr="disabled" class="gh-btn gh-btn--primary" style="margin-top:12px;">Activate supplier workspace</button>
            @else
                <p>Your business is approved. Ask the business owner to grant you supplier team access.</p>
            @endif
        </section>
    @elseif($vendor->supplier_requested_at)
        <section class="gh-card gh-card--pad">
            <span class="gh-badge gh-badge--warning">{{ $vendor->supplier_reviewed_at ? 'Supplier access paused' : 'Awaiting platform approval' }}</span>
            <p style="margin-top:12px;">{{ $vendor->supplier_reviewed_at ? 'Contact platform support to review supplier access. Your garage account has not been replaced.' : 'Your request has been submitted. A platform administrator must verify your business before you can publish listings, propose products or submit quotes.' }}</p>
            <p class="gh-muted">Requested {{ $vendor->supplier_requested_at->format('d M Y H:i') }}. Your current garage subscription and its expiry still apply; this does not start another trial.</p>
            @if(auth()->user()->hasRole('supplier_admin'))<a href="{{ route('supplier.catalog.index') }}" class="gh-btn" style="margin-top:12px;">Browse the catalog while you wait</a>@endif
        </section>
    @elseif($canRequest && in_array($vendor->status, ['active', 'trial']))
        <section class="gh-card gh-card--pad">
            <h2 class="gh-card__title">Add supplier module</h2>
            <ol style="list-style:decimal;padding-left:22px;margin:16px 0;line-height:1.8;"><li>Tell us what parts or products your business supplies.</li><li>Submit your existing business for platform approval.</li><li>After approval, create listings, respond to quote requests and fulfil supplier orders.</li></ol>
            <p class="gh-muted">Your current business contact information is used for the supplier profile. Check it in Settings before applying. No second login, new subscription or automatic charge is created. Your existing garage billing rules continue, and normal marketplace commission rules apply to supplier sales.</p>
            <form wire:submit="enable" style="margin-top:20px;display:grid;gap:16px;">
                <div><label for="supplier-notes" style="display:block;font-weight:600;margin-bottom:6px;">What will you supply?</label><textarea id="supplier-notes" wire:model="notes" class="gh-input" rows="4" minlength="10" maxlength="1000" required placeholder="For example: brake pads, filters and engine oils, available from our existing branch."></textarea></div>
                <label style="display:flex;align-items:flex-start;gap:10px;"><input type="checkbox" wire:model="consent" required style="margin-top:4px;"><span>I am authorised to apply for this business. I understand that selling requires platform approval and that my current garage subscription remains in effect.</span></label>
                <button type="submit" wire:loading.attr="disabled" class="gh-btn gh-btn--primary" style="justify-self:start;"><span wire:loading.remove wire:target="enable">Request supplier access</span><span wire:loading wire:target="enable">Submitting…</span></button>
            </form>
        </section>
    @else
        <section class="gh-card gh-card--pad"><h2 class="gh-card__title">Ask your business owner</h2><p>Only the garage owner can enable the supplier module. Once approved, they can grant supplier access to existing team members. Your business must also be active.</p></section>
    @endif
    <a href="{{ route('dashboard') }}" class="gh-btn" style="align-self:flex-start;">Back to garage</a>
</div>
