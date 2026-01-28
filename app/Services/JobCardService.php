<?php

namespace App\Services;

use App\Models\JobCard;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ClientNarration;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Commission;

class JobCardService
{
    public function createJobCard(array $data): JobCard
    {
        return DB::transaction(function () use ($data) {
            // Handle customer
            $customer = $this->handleCustomer($data);

            // Handle vehicle
            $vehicle = $this->handleVehicle($data, $customer->id);

            // Create job card
            $jobCard = JobCard::create([
                'staff_id' => $data['staff_id'],
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'vehicle_type_id' => $vehicle->vehicle_type_id,
                'image_attachment' => $data['image_attachment'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'notes' => $data['notes'] ?? null,
                'intake_datetime' => $data['intake_datetime'] ?? now(),
            ]);

            // If vehicle was just created during handleVehicle, associate it with this job card so it can be cleaned up later
            if (Schema::hasTable('vehicles') && isset($vehicle) && isset($vehicle->wasRecentlyCreated) && $vehicle->wasRecentlyCreated) {
                try {
                    $vehicle->job_card_id = $jobCard->id;
                    $vehicle->save();
                } catch (\Throwable $e) {
                    // ignore if column doesn't exist or save fails
                }
            }


            // Handle client narrations
            if (!empty($data['client_narrations']) && is_array($data['client_narrations'])) {
                foreach ($data['client_narrations'] as $narr) {
                    $issue = is_array($narr) ? ($narr['issue'] ?? null) : $narr;
                    if ($issue) {
                        ClientNarration::create([
                            'job_card_id' => $jobCard->id,
                            'issue' => $issue,
                        ]);
                    }
                }
            }

            return $jobCard->load(['customer', 'vehicle']);
        });
    }

    private function handleCustomer(array $data): Customer
    {
        // Try to find existing customer
        $customer = Customer::findByIdentifier(
            $data['phone'] ?? null,
            $data['email'] ?? null
        );

        if ($customer) {
            // Update existing customer with new information if provided
            $updateData = array_filter([
                'customer_name' => $data['customer_name'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'contact_person' => $data['contact_person'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            if (!empty($updateData)) {
                $customer->update($updateData);
            }

            // Update nature_of_customer to 'returning'
            $customer->update(['nature_of_customer' => 'returning']);
        } else {
            // Create new customer
            $customer = Customer::create([
                'phone' => $data['phone'],
                'customer_name' => $data['customer_name'],
                'company_name' => $data['company_name'] ?? null,
                'email' => $data['email'] ?? null,
                'contact_person' => $data['contact_person'] ?? null,
                'address' => $data['address'] ?? null,
                'nature_of_customer' => 'new'
            ]);
        }

        return $customer;
    }

    private function handleVehicle(array $data, int $customerId): Vehicle
    {
        if (empty($data['vehicle_type_id'])) {
            throw new \Exception('Vehicle type is required.');
        }
        // Try to find existing vehicle
        $vehicle = Vehicle::findByIdentifier(
            $data['number_plate'] ?? null,
            $data['chasis_number'] ?? null
        );

        if ($vehicle) {
            // Update vehicle information if provided
            $updateData = array_filter([
                'vehicle_type_id' => $data['vehicle_type_id'] ?? null,
                'vehicle_name' => $data['vehicle_name'] ?? null,
                'color' => $data['color'] ?? null,
                    'mileage' => $data['mileage'] ?? null,
                    'fuel_type' => $data['fuel_type'] ?? null,
                    'fuel_level' => $data['fuel_level'] ?? null,
                    'physical_condition' => $data['physical_condition'] ?? null,
                    'vin_number' => $data['vin_number'] ?? null,
            ]);

            // Always ensure vehicle_type_id is set if missing
            if (empty($vehicle->vehicle_type_id) && !empty($data['vehicle_type_id'])) {
                $updateData['vehicle_type_id'] = $data['vehicle_type_id'];
            }

            if (!empty($updateData)) {
                $vehicle->update($updateData);
            }

            // Update customer_id if different (vehicle ownership change)
            if ($vehicle->customer_id !== $customerId) {
                $vehicle->update(['customer_id' => $customerId]);
            }
        } else {
            // Create new vehicle
            $vehicle = Vehicle::create([
                'customer_id' => $customerId,
                'vehicle_type_id' => $data['vehicle_type_id'],
                'vehicle_name' => $data['vehicle_name'],
                'number_plate' => $data['number_plate'],
                'chasis_number' => $data['chasis_number'] ?? null,
                    'color' => $data['color'] ?? null,
                    'mileage' => $data['mileage'] ?? null,
                    'fuel_type' => $data['fuel_type'] ?? null,
                    'fuel_level' => $data['fuel_level'] ?? null,
                    'physical_condition' => $data['physical_condition'] ?? null,
                    'vin_number' => $data['vin_number'] ?? null,
            ]);
        }

        return $vehicle;
    }

    public function searchCustomers(string $query): array
    {
        $customers = Customer::where('customer_name', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        return $customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'label' => $customer->customer_name . ' (' . $customer->phone . ')',
                'customer_name' => $customer->customer_name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'company_name' => $customer->company_name,
                'contact_person' => $customer->contact_person,
                'address' => $customer->address,
            ];
        })->toArray();
    }

    public function searchVehicles(string $query, ?int $customerId = null): array
    {
        $vehicles = Vehicle::with(['customer', 'vehicleType'])
            ->where('number_plate', 'LIKE', "%{$query}%")
            ->orWhere('vehicle_name', 'LIKE', "%{$query}%")
            ->when($customerId, function ($q) use ($customerId) {
                return $q->orWhere('customer_id', $customerId);
            })
            ->limit(10)
            ->get();

        return $vehicles->map(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'label' => $vehicle->vehicle_name . ' (' . $vehicle->number_plate . ')',
                'vehicle_name' => $vehicle->vehicle_name,
                'number_plate' => $vehicle->number_plate,
                'chasis_number' => $vehicle->chasis_number,
                'color' => $vehicle->color,
                'vehicle_type_id' => $vehicle->vehicle_type_id,
                'customer_id' => $vehicle->customer_id,
                'customer_name' => $vehicle->customer->customer_name ?? null,
            ];
        })->toArray();
    }

    public function updateJobCard(int $jobCardId, array $data): ?\App\Models\JobCard
    {
        return DB::transaction(function () use ($jobCardId, $data) {
            $jobCard = JobCard::findOrFail($jobCardId);

            // Update customer and vehicle using existing handlers where appropriate
            $customer = $this->handleCustomer($data);
            $vehicle = $this->handleVehicle($data, $customer->id);

            $jobCard->update([
                'staff_id' => $data['staff_id'] ?? $jobCard->staff_id,
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'vehicle_type_id' => $vehicle->vehicle_type_id ?? $jobCard->vehicle_type_id,
                'image_attachment' => $data['image_attachment'] ?? $jobCard->image_attachment,
                'status' => $data['status'] ?? $jobCard->status,
                'notes' => $data['notes'] ?? $jobCard->notes,
                'intake_datetime' => $data['intake_datetime'] ?? $jobCard->intake_datetime,
            ]);

            // Replace client narrations if provided
            if (array_key_exists('client_narrations', $data)) {
                // remove old ones
                ClientNarration::where('job_card_id', $jobCard->id)->delete();
                if (!empty($data['client_narrations']) && is_array($data['client_narrations'])) {
                    foreach ($data['client_narrations'] as $narr) {
                        $issue = is_array($narr) ? ($narr['issue'] ?? null) : $narr;
                        if ($issue) {
                            ClientNarration::create([
                                'job_card_id' => $jobCard->id,
                                'issue' => $issue,
                            ]);
                        }
                    }
                }
            }

            return $jobCard->fresh()->load(['customer', 'vehicle']);
        });
    }

    public function deleteJobCard(int $jobCardId): bool
    {
        return DB::transaction(function () use ($jobCardId) {
            $jobCard = JobCard::find($jobCardId);
            if (!$jobCard) {
                return false;
            }

            // Remove related client narrations (if table exists)
            if (Schema::hasTable('client_narrations')) {
                ClientNarration::where('job_card_id', $jobCard->id)->delete();
            }

            // Delete invoices -> payments -> receipts (to avoid FK constraint errors)
            if (Schema::hasTable('invoices')) {
                $invoices = Invoice::where('job_card_id', $jobCard->id)->get();
                if ($invoices->count()) {
                    $invoiceIds = $invoices->pluck('id')->toArray();

                    if (Schema::hasTable('payments')) {
                        // Delete receipts attached to payments for these invoices
                        $payments = Payment::whereIn('invoice_id', $invoiceIds)->get();
                        if ($payments->count()) {
                            $paymentIds = $payments->pluck('id')->toArray();
                            if (Schema::hasTable('receipts')) {
                                Receipt::whereIn('payment_id', $paymentIds)->delete();
                            }
                            Payment::whereIn('id', $paymentIds)->delete();
                        }
                    }

                    // Delete invoices
                    Invoice::whereIn('id', $invoiceIds)->delete();
                }
            }

            // Delete commissions for this job card (if table exists)
            if (Schema::hasTable('commissions')) {
                Commission::where('job_card_id', $jobCard->id)->delete();
            }

            // Delegate deletion responsibilities to the JobCard model deleting hook.
            // The model hook will dissociate the vehicle reference and only remove
            // the vehicle if no other job cards reference it, avoiding FK issues.
            $jobCard->delete();

            return true;
        });
    }
}
