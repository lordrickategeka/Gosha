<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\NumberGeneratorHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ClientNarration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Commission;

class JobCard extends Model
{
    use HasFactory;

    public function vehicleItems()
    {
        // Get items for this job card's vehicle and customer
        return $this->vehicle
            ? $this->vehicle->vehicleItems()->where('customer_id', $this->customer_id)
            : collect();
    }

    protected $fillable = [
        'job_card_number',
        'staff_id',
        'customer_id',
        'vehicle_id',
        'vehicle_type_id',
        'image_attachment',
        'status',
        'notes',
        'started_at',
        'completed_at',
        'collected_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'collected_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jobCard) {
            $jobCard->job_card_number = NumberGeneratorHelper::generateJobCardNumber();
        });

        static::deleting(function ($jobCard) {
            // Capture and dissociate this job card's vehicle reference to avoid FK issues
            $vehicleId = null;
            if (Schema::hasColumn('job_cards', 'vehicle_id')) {
                $vehicleId = DB::table('job_cards')->where('id', $jobCard->id)->value('vehicle_id');
                DB::table('job_cards')->where('id', $jobCard->id)->update(['vehicle_id' => null]);
            }
            // Defensive deletes for related records when a JobCard is deleted directly
            if (Schema::hasTable('client_narrations')) {
                ClientNarration::where('job_card_id', $jobCard->id)->delete();
            }

            if (Schema::hasTable('invoices')) {
                $invoices = Invoice::where('job_card_id', $jobCard->id)->get();
                if ($invoices->count()) {
                    $invoiceIds = $invoices->pluck('id')->toArray();
                    if (Schema::hasTable('payments')) {
                        $payments = Payment::whereIn('invoice_id', $invoiceIds)->get();
                        if ($payments->count()) {
                            $paymentIds = $payments->pluck('id')->toArray();
                            if (Schema::hasTable('receipts')) {
                                Receipt::whereIn('payment_id', $paymentIds)->delete();
                            }
                            Payment::whereIn('id', $paymentIds)->delete();
                        }
                    }
                    Invoice::whereIn('id', $invoiceIds)->delete();
                }
            }

            if (Schema::hasTable('commissions')) {
                Commission::where('job_card_id', $jobCard->id)->delete();
            }

            if (Schema::hasTable('vehicle_items')) {
                DB::table('vehicle_items')->where('job_card_id', $jobCard->id)->delete();
            }

            // Delete the vehicle only if this job card referenced one and
            // no other job cards reference the same vehicle.
            if ($vehicleId && Schema::hasTable('vehicles')) {
                $otherCount = DB::table('job_cards')
                    ->where('vehicle_id', $vehicleId)
                    ->where('id', '!=', $jobCard->id)
                    ->count();

                if ($otherCount === 0) {
                    DB::table('vehicles')->where('id', $vehicleId)->delete();
                }
            }

            if (Schema::hasTable('job_card_service_types')) {
                DB::table('job_card_service_types')->where('job_card_id', $jobCard->id)->delete();
            }

            if (Schema::hasTable('workshop_jobcards')) {
                DB::table('workshop_jobcards')->where('jobcard_id', $jobCard->id)->delete();
            }
        });
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'service_job_id');
    }

    public function commission()
    {
        return $this->hasOne(Commission::class);
    }

    public function customer(): BelongsTo
    {

        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function workshopJobcards()
    {
        return $this->hasMany(WorkshopJobcard::class, 'jobcard_id');
    }

    public function clientNarrations(): HasMany
    {
        return $this->hasMany(ClientNarration::class);
    }
}


