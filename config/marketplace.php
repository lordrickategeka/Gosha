<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default commission rate (percent)
    |--------------------------------------------------------------------------
    | Fallback used by MarketplaceCommissionService when the supplier has no
    | active platform billing plan rate. Override per-supplier via BillingService.
    */
    'default_commission_rate' => env('MARKETPLACE_DEFAULT_COMMISSION_RATE', 5.0),

    /*
    | Default currency for new listings / RFQs / POs.
    */
    'default_currency' => env('MARKETPLACE_DEFAULT_CURRENCY', 'UGX'),

    /*
    | When true, suppliers may submit new catalog products (unverified, pending
    | platform-admin moderation). When false, only verified catalog is selectable.
    */
    'allow_supplier_catalog_submissions' => true,
];
