<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartInstallationHistory extends Model
{
    use HasFactory;

    protected $table = 'part_installation_history';

    protected $fillable = [
        'work_order_id',
        'work_order_item_id',
        'vehicle_id',
        'part_id',
        'part_oem_number_id',
        'supplier_part_id',
        'work_order_part_source_id',
        'inventory_item_id',
        'technician_id',
        'installed_at',
        'fit_status',
        'was_returned',
        'fitment_notes',
        'failure_reason',
        'recorded_by',
    ];

    protected $casts = [
        'installed_at' => 'datetime',
        'was_returned' => 'boolean',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function workOrderItem(): BelongsTo
    {
        return $this->belongsTo(WorkOrderItem::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function oemNumber(): BelongsTo
    {
        return $this->belongsTo(PartOemNumber::class, 'part_oem_number_id');
    }

    public function supplierPart(): BelongsTo
    {
        return $this->belongsTo(SupplierPart::class);
    }

    public function workOrderPartSource(): BelongsTo
    {
        return $this->belongsTo(WorkOrderPartSource::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
