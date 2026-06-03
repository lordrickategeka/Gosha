<?php

namespace App\Livewire\Commissions;

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class CommissionsComponent extends Component
{
    use WithPagination;

    public $user = '';
    public $status = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $showModal = false;
    public $selectedCommission = null;
    public $showRuleModal = false;
    public $selectedRule = null;

    // Rule form fields
    public $ruleName = '';
    public $ruleRole = 'technician';
    public $ruleType = 'percentage';
    public $ruleValue = 0;
    public $ruleAppliesTo = 'labor';
    public $ruleMinimumThreshold = 0;
    public $ruleIsActive = true;
    public $ruleDescription = '';

    protected $queryString = ['user' => ['except' => ''], 'status' => ['except' => '']];

    public function markPaid(Commission $commission)
    {
        $this->authorize('pay_commissions');
        $commission->markAsPaid();
        session()->flash('success', 'Commission marked as paid.');
    }

    public function approve(Commission $commission)
    {
        $this->authorize('approve_commissions');
        $commission->approve();
        session()->flash('success', 'Commission approved.');
    }

    public function bulkApprove()
    {
        $this->authorize('approve_commissions');
        Commission::where('status', 'pending')
            ->when($this->user, fn($q) => $q->where('user_id', $this->user))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        session()->flash('success', 'Selected commissions approved.');
    }

    public function bulkMarkPaid()
    {
        $this->authorize('pay_commissions');
        Commission::whereIn('status', ['pending', 'approved'])
            ->when($this->user, fn($q) => $q->where('user_id', $this->user))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        session()->flash('success', 'Selected commissions marked as paid.');
    }

    public function openApproveModal(Commission $commission)
    {
        $this->selectedCommission = $commission;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedCommission = null;
    }

    public function getTechniciansProperty()
    {
        return User::role(['technician', 'wash-attendant'])
            ->where('vendor_id', auth()->user()->vendor_id)
            ->get();
    }

    public function getRulesProperty()
    {
        return CommissionRule::with('branch')
            ->when(!auth()->user()->is_super_admin, fn($q) => $q->where('vendor_id', auth()->user()->vendor_id))
            ->orderBy('name')
            ->get();
    }

    public function canApprove(): bool
    {
        return Gate::allows('approve_commissions');
    }

    public function canPay(): bool
    {
        return Gate::allows('pay_commissions');
    }

    public function canManageRules(): bool
    {
        return Gate::allows('manage_commission_rules');
    }

    // Rule CRUD methods
    public function openRuleModal(CommissionRule $rule = null)
    {
        if ($rule) {
            $this->selectedRule = $rule;
            $this->ruleName = $rule->name;
            $this->ruleRole = $rule->role;
            $this->ruleType = $rule->type;
            $this->ruleValue = $rule->value;
            $this->ruleAppliesTo = $rule->applies_to;
            $this->ruleMinimumThreshold = $rule->minimum_threshold ?? 0;
            $this->ruleIsActive = $rule->is_active;
            $this->ruleDescription = $rule->description ?? '';
        } else {
            $this->resetRuleForm();
        }
        $this->showRuleModal = true;
    }

    public function closeRuleModal()
    {
        $this->showRuleModal = false;
        $this->selectedRule = null;
        $this->resetRuleForm();
    }

    public function resetRuleForm()
    {
        $this->ruleName = '';
        $this->ruleRole = 'technician';
        $this->ruleType = 'percentage';
        $this->ruleValue = 0;
        $this->ruleAppliesTo = 'labor';
        $this->ruleMinimumThreshold = 0;
        $this->ruleIsActive = true;
        $this->ruleDescription = '';
    }

    public function saveRule()
    {
        $this->authorize('manage_commission_rules');

        $validated = $this->validate([
            'ruleName' => 'required|string|max:255',
            'ruleRole' => 'required|in:technician,wash-attendant,advisor',
            'ruleType' => 'required|in:percentage,flat',
            'ruleValue' => 'required|numeric|min:0',
            'ruleAppliesTo' => 'required|in:labor,parts,total,wash',
            'ruleMinimumThreshold' => 'nullable|numeric|min:0',
            'ruleIsActive' => 'boolean',
            'ruleDescription' => 'nullable|string',
        ]);

        $data = [
            'name' => $this->ruleName,
            'role' => $this->ruleRole,
            'type' => $this->ruleType,
            'value' => $this->ruleValue,
            'applies_to' => $this->ruleAppliesTo,
            'minimum_threshold' => $this->ruleMinimumThreshold ?: null,
            'is_active' => $this->ruleIsActive,
            'description' => $this->ruleDescription,
            'vendor_id' => auth()->user()->vendor_id,
        ];

        if ($this->selectedRule) {
            $this->selectedRule->update($data);
            session()->flash('success', 'Commission rule updated.');
        } else {
            CommissionRule::create($data);
            session()->flash('success', 'Commission rule created.');
        }

        $this->closeRuleModal();
    }

    public function deleteRule(CommissionRule $rule)
    {
        $this->authorize('manage_commission_rules');
        $rule->delete();
        session()->flash('success', 'Commission rule deleted.');
    }

    public function toggleRuleStatus(CommissionRule $rule)
    {
        $this->authorize('manage_commission_rules');
        $rule->update(['is_active' => !$rule->is_active]);
    }

    public function render()
    {
        $commissions = Commission::with(['user', 'commissionRule'])
            ->when($this->user, fn($q) => $q->where('user_id', $this->user))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(20);

        $vendorId = auth()->user()->vendor_id;
        $totals = [
            'pending' => Commission::where('status', 'pending')
                ->whereHas('user', fn($q) => $q->where('vendor_id', $vendorId))
                ->sum('commission_amount'),
            'approved' => Commission::where('status', 'approved')
                ->whereHas('user', fn($q) => $q->where('vendor_id', $vendorId))
                ->sum('commission_amount'),
            'paid_month' => Commission::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereHas('user', fn($q) => $q->where('vendor_id', $vendorId))
                ->sum('commission_amount'),
            'pending_count' => Commission::where('status', 'pending')
                ->whereHas('user', fn($q) => $q->where('vendor_id', $vendorId))
                ->count(),
        ];

        $stats = [
            'this_month' => Commission::whereHas('user', fn($q) => $q->where('vendor_id', $vendorId))
                ->whereMonth('created_at', now()->month)
                ->sum('commission_amount'),
            'total_paid' => Commission::where('status', 'paid')
                ->whereHas('user', fn($q) => $q->where('vendor_id', $vendorId))
                ->sum('commission_amount'),
        ];

        return view('livewire.commissions.commissions-component', compact('commissions', 'totals', 'stats'))
            ->layout('components.layouts.app', ['title' => 'Commissions']);
    }
}
