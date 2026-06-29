<?php

namespace App\Domains\Organization\Livewire\Settings;

use App\Domains\Platform\Models\Vendor;
use Livewire\Component;

class SettingsComponent extends Component
{
    public $tab = 'general';

    // General settings
    public $businessName = '';
    public $phone = '';
    public $email = '';
    public $address = '';
    public $currency = 'UGX';
    public $timezone = 'Africa/Kampala';

    // Invoice settings
    public $invoicePrefix = 'INV';
    public $invoiceFooter = '';
    public $defaultDueDays = 7;
    public $taxRate = 18;

    // Notification settings
    public $emailNotifications = true;
    public $smsNotifications = false;

    // Closure settings
    public $closureEnabled = false;
    public $closureTime = '18:00';

    public function mount()
    {
        $vendor = auth()->user()->vendor;
        if ($vendor) {
            $settings = $vendor->settings ?? [];
            $this->businessName = $vendor->name;
            $this->phone = $vendor->phone;
            $this->email = $vendor->email;
            $this->address = $vendor->address;
            $this->currency = $settings['currency'] ?? 'UGX';
            $this->timezone = $settings['timezone'] ?? 'Africa/Kampala';
            $this->invoicePrefix = $settings['invoice_prefix'] ?? 'INV';
            $this->invoiceFooter = $settings['invoice_footer'] ?? '';
            $this->defaultDueDays = $settings['default_due_days'] ?? 7;
            $this->taxRate = $settings['tax_rate'] ?? 18;
            $this->emailNotifications = $settings['email_notifications'] ?? true;
            $this->smsNotifications = $settings['sms_notifications'] ?? false;
            $this->closureEnabled = $settings['closure_enabled'] ?? false;
            $this->closureTime = $settings['closure_time'] ?? '18:00';
        }
    }

    public function saveGeneral()
    {
        $this->validate([
            'businessName' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
        ]);

        $vendor = auth()->user()->vendor;
        $vendor->update([
            'name' => $this->businessName,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
        ]);

        $settings = $vendor->settings ?? [];
        $settings['currency'] = $this->currency;
        $settings['timezone'] = $this->timezone;
        $settings['closure_enabled'] = $this->closureEnabled;
        $settings['closure_time'] = $this->closureTime;
        $vendor->update(['settings' => $settings]);

        session()->flash('success', 'General settings saved.');
    }

    public function saveInvoice()
    {
        $vendor = auth()->user()->vendor;
        $settings = $vendor->settings ?? [];
        $settings['invoice_prefix'] = $this->invoicePrefix;
        $settings['invoice_footer'] = $this->invoiceFooter;
        $settings['default_due_days'] = $this->defaultDueDays;
        $settings['tax_rate'] = $this->taxRate;
        $vendor->update(['settings' => $settings]);

        session()->flash('success', 'Invoice settings saved.');
    }

    public function saveNotifications()
    {
        $vendor = auth()->user()->vendor;
        $settings = $vendor->settings ?? [];
        $settings['email_notifications'] = $this->emailNotifications;
        $settings['sms_notifications'] = $this->smsNotifications;
        $vendor->update(['settings' => $settings]);

        session()->flash('success', 'Notification settings saved.');
    }

    public function render()
    {
        return view('livewire.settings.settings-component')
            ->layout('components.layouts.app', ['title' => 'Settings']);
    }
}
