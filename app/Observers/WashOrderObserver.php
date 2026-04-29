<?php

namespace App\Observers;

use App\Enums\WashOrderStatus;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\WashOrder;

class WashOrderObserver
{
    /**
     * When a wash order transitions to 'completed', automatically create
     * commissions for the assigned attendant if a matching rule exists.
     */
    public function updated(WashOrder $washOrder): void
    {
        if (! $washOrder->wasChanged('status')) {
            return;
        }

        $newStatus = $washOrder->status instanceof WashOrderStatus
            ? $washOrder->status
            : WashOrderStatus::tryFrom($washOrder->status);

        if ($newStatus !== WashOrderStatus::Completed) {
            return;
        }

        if (! $washOrder->assigned_attendant_id) {
            return;
        }

        $attendant = $washOrder->assignedAttendant;
        if (! $attendant) {
            return;
        }

        $rule = CommissionRule::where('branch_id', $washOrder->branch_id)
            ->where('role', 'wash_attendant')
            ->where('is_active', true)
            ->first();

        if (! $rule) {
            return;
        }

        // Avoid double-creating commissions if the order already has one
        $alreadyExists = Commission::where('reference_type', 'wash_order')
            ->where('reference_id', $washOrder->id)
            ->exists();

        if ($alreadyExists) {
            return;
        }

        Commission::createFromWashOrder($washOrder, $attendant, $rule);
    }
}
