<?php

namespace App\Domains\Marketplace\Livewire\Supplier;

use App\Domains\Marketplace\Services\SupplierModuleService;
use Livewire\Component;

class EnableModule extends Component
{
    public string $notes = '';

    public bool $consent = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->is_active && ! auth()->user()->is_platform_user && auth()->user()->vendor_id, 403);
    }

    public function enable(): void
    {
        $service = app(SupplierModuleService::class);
        abort_unless($service->canRequest(auth()->user()), 403, 'Ask your business owner to enable supplier access.');
        $this->notes = trim($this->notes);
        $this->validate(['notes' => 'required|string|min:10|max:1000', 'consent' => 'accepted']);
        $service->request(auth()->user(), $this->notes);
        auth()->user()->unsetRelation('vendor');
        session()->flash('success', 'Supplier module requested. Your garage stays available while the platform reviews your business.');
    }

    public function activate(): void
    {
        $user = auth()->user();
        $service = app(SupplierModuleService::class);
        abort_unless($service->canRequest($user) && $user->vendor?->isVerifiedSupplier(), 403);
        $service->request($user, 'Owner activated the existing verified supplier workspace.');
        $user->unsetRelation('vendor');
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user?->is_active && ! $user->is_platform_user && $user->vendor_id, 403);

        return view('livewire.marketplace.supplier.enable-module', [
            'vendor' => $user->vendor()->firstOrFail(),
            'canRequest' => app(SupplierModuleService::class)->canRequest($user),
        ])->layout('components.layouts.app', ['title' => 'Supplier module']);
    }
}
