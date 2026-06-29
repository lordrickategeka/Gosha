<?php

namespace App\Domains\Platform\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformFeature extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'is_core',
        'addon_price',
        'addon_billing',
        'sort_order',
    ];

    protected $casts = [
        'is_core' => 'boolean',
        'addon_price' => 'decimal:2',
    ];

    // Feature code constants
    const WORK_ORDERS = 'work_orders';
    const WASH_ORDERS = 'wash_orders';
    const COMBO_FLOW = 'combo_flow';
    const INVENTORY = 'inventory';
    const INVOICING = 'invoicing';
    const PAYMENTS = 'payments';
    const APPOINTMENTS = 'appointments';
    const CUSTOMERS = 'customers';
    const REPORTS_BASIC = 'reports_basic';
    const REPORTS_ADVANCED = 'reports_advanced';
    const MULTI_BRANCH = 'multi_branch';
    const STAFF_MANAGEMENT = 'staff_management';
    const COMMISSIONS = 'commissions';
    const SMS_NOTIFICATIONS = 'sms_notifications';
    const EMAIL_NOTIFICATIONS = 'email_notifications';
    const API_ACCESS = 'api_access';
    const CUSTOM_BRANDING = 'custom_branding';
    const PRIORITY_SUPPORT = 'priority_support';
    const DATA_EXPORT = 'data_export';
    const AUDIT_LOGS = 'audit_logs';

    public function scopeCore($query)
    {
        return $query->where('is_core', true);
    }

    public function scopeAddons($query)
    {
        return $query->where('is_core', false)->where('addon_price', '>', 0);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public static function getAllGroupedByCategory(): array
    {
        return self::orderBy('sort_order')
            ->get()
            ->groupBy('category')
            ->toArray();
    }
}
