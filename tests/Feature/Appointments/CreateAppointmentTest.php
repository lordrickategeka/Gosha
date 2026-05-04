<?php

namespace Tests\Feature\Appointments;

use App\Livewire\Appointments\CreateAppointmentsComponent;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateAppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Vendor $vendor;
    protected Branch $branch;
    protected Customer $customer;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $viewPerm   = Permission::create(['name' => 'view appointments', 'guard_name' => 'web']);
        $createPerm = Permission::create(['name' => 'create appointments', 'guard_name' => 'web']);
        $managerRole = Role::create(['name' => 'branch-manager', 'guard_name' => 'web']);
        $managerRole->givePermissionTo([$viewPerm, $createPerm]);

        $this->vendor = Vendor::create([
            'name'   => 'Test Vendor',
            'slug'   => 'test-vendor',
            'email'  => 'vendor@test.com',
            'status' => 'active',
        ]);

        $this->branch = Branch::create([
            'vendor_id' => $this->vendor->id,
            'name'      => 'Main Branch',
            'is_active' => true,
            'is_main'   => true,
        ]);

        $this->user = User::factory()->create([
            'vendor_id' => $this->vendor->id,
        ]);
        $this->user->assignRole($managerRole);
        $this->user->branches()->attach($this->branch->id, ['is_primary' => true]);

        $this->customer = Customer::withoutGlobalScopes()->create([
            'vendor_id' => $this->vendor->id,
            'name'      => 'Test Customer',
            'phone'     => '0712345678',
        ]);

        $this->vehicle = Vehicle::create([
            'customer_id'         => $this->customer->id,
            'registration_number' => 'KAA123A',
            'make'                => 'Toyota',
            'model'               => 'Corolla',
        ]);
    }

    protected function actingAsUser(): static
    {
        $this->actingAs($this->user);
        session(['current_branch_id' => $this->branch->id]);
        return $this;
    }

    // ─── Happy Path ────────────────────────────────────────────────────────

    public function test_authenticated_user_can_access_create_appointment_page(): void
    {
        $this->actingAsUser();

        $response = $this->get(route('appointments.create'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('appointments.create-appointments-component');
    }

    public function test_appointment_is_created_with_valid_data(): void
    {
        $this->actingAsUser();

        $tomorrow = now()->addDay()->format('Y-m-d');

        Livewire::test(CreateAppointmentsComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('type', 'service')
            ->set('scheduled_date', $tomorrow)
            ->set('scheduled_time', '10:00')
            ->set('duration_minutes', 60)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('appointments.index'));

        $this->assertDatabaseHas('appointments', [
            'branch_id'        => $this->branch->id,
            'customer_id'      => $this->customer->id,
            'vehicle_id'       => $this->vehicle->id,
            'type'             => 'service',
            'duration_minutes' => 60,
            'status'           => 'scheduled',
        ]);
    }

    public function test_appointment_status_defaults_to_scheduled(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('type', 'service')
            ->set('scheduled_date', now()->addDay()->format('Y-m-d'))
            ->set('scheduled_time', '09:00')
            ->set('duration_minutes', 60)
            ->call('save');

        $this->assertEquals('scheduled', Appointment::first()->status);
    }

    public function test_duration_minutes_is_persisted_correctly(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('type', 'service')
            ->set('scheduled_date', now()->addDay()->format('Y-m-d'))
            ->set('scheduled_time', '09:00')
            ->set('duration_minutes', 120)
            ->call('save');

        $this->assertEquals(120, Appointment::first()->duration_minutes);
    }

    public function test_service_notes_are_saved_when_notes_provided(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('type', 'service')
            ->set('scheduled_date', now()->addDay()->format('Y-m-d'))
            ->set('scheduled_time', '09:00')
            ->set('duration_minutes', 60)
            ->set('notes', 'Please check brake pads.')
            ->call('save');

        $this->assertDatabaseHas('appointments', [
            'service_notes' => 'Please check brake pads.',
        ]);
    }

    public function test_wash_type_appointment_is_created_successfully(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('type', 'wash')
            ->set('scheduled_date', now()->addDay()->format('Y-m-d'))
            ->set('scheduled_time', '14:00')
            ->set('duration_minutes', 30)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('appointments', [
            'type' => 'wash',
        ]);
    }

    public function test_mount_sets_scheduled_date_to_tomorrow(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->assertSet('scheduled_date', now()->addDay()->format('Y-m-d'));
    }

    // ─── Validation Errors ──────────────────────────────────────────────────

    public function test_save_requires_customer_id(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('scheduled_date', now()->addDay()->format('Y-m-d'))
            ->set('scheduled_time', '09:00')
            ->call('save')
            ->assertHasErrors(['customer_id']);
    }

    public function test_save_requires_vehicle_id(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('scheduled_date', now()->addDay()->format('Y-m-d'))
            ->set('scheduled_time', '09:00')
            ->call('save')
            ->assertHasErrors(['vehicle_id']);
    }

    public function test_past_date_fails_validation(): void
    {
        $this->actingAsUser();

        $yesterday = now()->subDay()->format('Y-m-d');

        Livewire::test(CreateAppointmentsComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('scheduled_date', $yesterday)
            ->set('scheduled_time', '10:00')
            ->call('save')
            ->assertHasErrors(['scheduled_date']);
    }

    public function test_today_is_a_valid_scheduled_date(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('type', 'diagnostics')
            ->set('scheduled_date', now()->format('Y-m-d'))
            ->set('scheduled_time', '09:00')
            ->set('duration_minutes', 60)
            ->call('save')
            ->assertHasNoErrors(['scheduled_date']);
    }

    public function test_duration_below_minimum_fails_validation(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('scheduled_date', now()->addDay()->format('Y-m-d'))
            ->set('scheduled_time', '09:00')
            ->set('duration_minutes', 10) // min is 15
            ->call('save')
            ->assertHasErrors(['duration_minutes']);
    }

    public function test_invalid_type_fails_validation(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('type', 'repair') // not in enum anymore
            ->set('scheduled_date', now()->addDay()->format('Y-m-d'))
            ->set('scheduled_time', '09:00')
            ->set('duration_minutes', 60)
            ->call('save')
            ->assertHasErrors(['type']);
    }

    public function test_scheduled_time_is_required(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('scheduled_date', now()->addDay()->format('Y-m-d'))
            ->set('scheduled_time', '')
            ->call('save')
            ->assertHasErrors(['scheduled_time']);
    }

    // ─── Customer/Vehicle event listeners ────────────────────────────────────

    public function test_customer_selected_event_sets_customer_id(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->dispatch('customerSelected', customerId: $this->customer->id)
            ->assertSet('customer_id', $this->customer->id)
            ->assertSet('vehicle_id', ''); // resets vehicle on new customer
    }

    public function test_vehicle_selected_event_sets_vehicle_id(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateAppointmentsComponent::class)
            ->dispatch('vehicleSelected', vehicleId: $this->vehicle->id)
            ->assertSet('vehicle_id', $this->vehicle->id);
    }
}
