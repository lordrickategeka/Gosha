<?php

namespace Database\Seeders;

use App\Domains\Platform\Models\PricingPlan;
use Illuminate\Database\Seeder;

class PricingPlanSeeder extends Seeder
{
    public function run(): void
    {
        if (PricingPlan::query()->exists()) {
            return;
        }

        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'For a single branch getting off the ground.',
                'sort_order' => 1,
                'billing_model' => PricingPlan::MODEL_SUBSCRIPTION,
                'base_price' => 50000,
                'currency' => 'UGX',
                'billing_cycle' => PricingPlan::CYCLE_MONTHLY,
                'max_branches' => 1,
                'max_users' => 5,
                'has_trial' => true,
                'trial_days' => 14,
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'For multi-branch operations that need room to grow.',
                'sort_order' => 2,
                'billing_model' => PricingPlan::MODEL_SUBSCRIPTION,
                'base_price' => 150000,
                'currency' => 'UGX',
                'billing_cycle' => PricingPlan::CYCLE_MONTHLY,
                'max_branches' => 5,
                'max_users' => 25,
                'has_trial' => true,
                'trial_days' => 14,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Unlimited branches and users, billed annually.',
                'sort_order' => 3,
                'billing_model' => PricingPlan::MODEL_SUBSCRIPTION,
                'base_price' => 1500000,
                'currency' => 'UGX',
                'billing_cycle' => PricingPlan::CYCLE_YEARLY,
                'has_trial' => false,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            PricingPlan::create($plan);
        }
    }
}
