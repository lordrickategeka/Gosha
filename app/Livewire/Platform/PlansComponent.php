<?php

namespace App\Livewire\Platform;

use Livewire\Component;
use App\Models\PricingPlan;


class PlansComponent extends Component
{
    public $showCreateModal = false;
    public $editingPlan = null;

    // Plan form data
    public $name = '';
    public $slug = '';
    public $description = '';
    public $billing_model = 'subscription';
    public $base_price = 0;
    public $currency = 'UGX';
    public $billing_cycle = 'monthly';
    public $billing_cycle_days = null;

    // Commission
    public $commission_rate = 0;
    public $commission_base = 'payment_received';
    public $commission_min = null;
    public $commission_max = null;

    // Pay-per-use
    public $price_per_work_order = 0;
    public $price_per_wash_order = 0;
    public $price_per_invoice = 0;
    public $price_per_user = 0;
    public $price_per_branch = 0;
    public $price_per_sms = 0;

    // Limits
    public $max_branches = null;
    public $max_users = null;
    public $max_customers = null;
    public $max_work_orders_per_month = null;
    public $max_wash_orders_per_month = null;
    public $max_inventory_items = null;

    // Trial & Setup
    public $has_trial = true;
    public $trial_days = 14;
    public $setup_fee = 0;

    // Flags
    public $is_active = true;
    public $is_featured = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'billing_model' => 'required|in:free,one_time,subscription,commission,hybrid,pay_per_use,custom',
        'base_price' => 'required|numeric|min:0',
        'billing_cycle' => 'required',
    ];

    public function updatedName($value)
    {
        $this->slug = \Str::slug($value);
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEdit(PricingPlan $plan)
    {
        $this->editingPlan = $plan;
        $this->fill($plan->toArray());
        $this->showCreateModal = true;
    }

    public function resetForm()
    {
        $this->editingPlan = null;
        $this->reset([
            'name', 'slug', 'description', 'billing_model', 'base_price', 'currency',
            'billing_cycle', 'billing_cycle_days', 'commission_rate', 'commission_base',
            'commission_min', 'commission_max', 'price_per_work_order', 'price_per_wash_order',
            'price_per_invoice', 'price_per_user', 'price_per_branch', 'price_per_sms',
            'max_branches', 'max_users', 'max_customers', 'max_work_orders_per_month',
            'max_wash_orders_per_month', 'max_inventory_items', 'has_trial', 'trial_days',
            'setup_fee', 'is_active', 'is_featured'
        ]);
        $this->billing_model = 'subscription';
        $this->currency = 'UGX';
        $this->billing_cycle = 'monthly';
        $this->has_trial = true;
        $this->trial_days = 14;
        $this->is_active = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => $this->slug ?: \Str::slug($this->name),
            'description' => $this->description,
            'billing_model' => $this->billing_model,
            'base_price' => $this->base_price,
            'currency' => $this->currency,
            'billing_cycle' => $this->billing_cycle,
            'billing_cycle_days' => $this->billing_cycle_days,
            'commission_rate' => $this->commission_rate,
            'commission_base' => $this->commission_base,
            'commission_min' => $this->commission_min,
            'commission_max' => $this->commission_max,
            'price_per_work_order' => $this->price_per_work_order,
            'price_per_wash_order' => $this->price_per_wash_order,
            'price_per_invoice' => $this->price_per_invoice,
            'price_per_user' => $this->price_per_user,
            'price_per_branch' => $this->price_per_branch,
            'price_per_sms' => $this->price_per_sms,
            'max_branches' => $this->max_branches,
            'max_users' => $this->max_users,
            'max_customers' => $this->max_customers,
            'max_work_orders_per_month' => $this->max_work_orders_per_month,
            'max_wash_orders_per_month' => $this->max_wash_orders_per_month,
            'max_inventory_items' => $this->max_inventory_items,
            'has_trial' => $this->has_trial,
            'trial_days' => $this->trial_days,
            'setup_fee' => $this->setup_fee,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
        ];

        if ($this->editingPlan) {
            $this->editingPlan->update($data);
            session()->flash('success', 'Pricing plan updated.');
        } else {
            PricingPlan::create($data);
            session()->flash('success', 'Pricing plan created.');
        }

        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function toggleStatus(PricingPlan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
    }

    public function toggleFeatured(PricingPlan $plan)
    {
        $plan->update(['is_featured' => !$plan->is_featured]);
    }

    public function setDefault(PricingPlan $plan)
    {
        PricingPlan::where('is_default', true)->update(['is_default' => false]);
        $plan->update(['is_default' => true]);
        session()->flash('success', "{$plan->name} is now the default plan.");
    }

    public function delete(PricingPlan $plan)
    {
        if ($plan->subscriptions()->exists()) {
            session()->flash('error', 'Cannot delete plan with active subscriptions.');
            return;
        }
        $plan->delete();
        session()->flash('success', 'Plan deleted.');
    }

    public function getPlansProperty()
    {
        return PricingPlan::withCount('subscriptions')
            ->orderBy('sort_order')
            ->orderBy('base_price')
            ->get();
    }

    public function render()
    {
        return view('livewire.platform.plans-component')
            ->layout('components.layouts.app', ['title' => 'Pricing Plans']);
    }
}
