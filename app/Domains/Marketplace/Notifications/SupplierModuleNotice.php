<?php

namespace App\Domains\Marketplace\Notifications;

use Illuminate\Notifications\Notification;

class SupplierModuleNotice extends Notification
{
    public function __construct(public int $vendorId, public string $businessName, public string $event) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        [$title, $message] = match ($this->event) {
            'requested' => ['Supplier module review needed', $this->businessName.' has requested supplier access for its existing garage. Review the application in Vendors.'],
            'approved' => ['Supplier module approved', 'Your business is verified for supplier sales. Open Supplier module to continue using your existing account and garage subscription.'],
            default => ['Supplier access paused', 'Supplier verification has been revoked. Contact platform support. Your garage login and records are retained.'],
        };

        return ['type' => 'supplier_module_'.$this->event, 'title' => $title, 'message' => $message,
            'domain' => 'marketplace', 'severity' => $this->event === 'approved' ? 'success' : 'info',
            'url' => $this->event === 'requested' ? route('platform.vendors.show', $this->vendorId) : route('supplier.enable'),
            'requires_action' => $this->event === 'requested'];
    }
}
