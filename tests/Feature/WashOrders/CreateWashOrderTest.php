<?php

namespace Tests\Feature\WashOrders;

use App\Livewire\WashOrders\CreateWashOrdersComponent;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vendor;
use App\Models\WashOrder;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateWashOrderTest extends TestCase
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

        $createPerm = Permission::create(['name' => 'create_wash_orders', 'guard_name' => 'web']);
        $managerRole = Role::create(['name' => 'branch-manager', 'guard_name' => 'web']);
        $managerRole->givePermissionTo($createPerm);

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

    public function test_authenticated_user_can_access_create_wash_order_page(): void
    {
        $this->actingAsUser();

        $response = $this->get(route('wash-orders.create'));

        $response->assertStatus(200);
        $response->assertSeeLivewire('wash-orders.create-wash-orders-component');
    }

    public function test_wash_order_is_created_with_valid_data(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateWashOrdersComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('wash_type', 'basic')
            ->set('priority', 'normal')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('wash-orders.index'));

        $this->assertDatabaseHas('wash_orders', [
            'branch_id'   => $this->branch->id,
            'customer_id' => $this->customer->id,
            'vehicle_id'  => $this->vehicle->id,
            'wash_type'   => 'basic',
            'status'      => 'queued',
            'source'      => 'walk_in',
        ]);
    }

    public function test_wash_order_gets_queue_position_on_creation(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateWashOrdersComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('wash_type', 'basic')
            ->call('save');

        $washOrder = WashOrder::first();
        $this->assertNotNull($washOrder->queue_position);
        $this->assertGreaterThan(0, $washOrder->queue_position);
    }

    public function test_second_wash_order_gets_incremented_queue_position(): void
    {
        $this->actingAsUser();

        // First order
        Livewire::test(CreateWashOrdersComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('wash_type', 'basic')
            ->call('save');

        // Second order
        Livewire::test(CreateWashOrdersComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('wash_type', 'premium')
            ->call('save');

        $orders = WashOrder::orderBy('queue_position')->get();
        $this->assertEquals(1, $orders[0]->queue_position);
        $this->assertEquals(2, $orders[1]->queue_position);
    }

    public function test_wash_order_has_queued_at_timestamp(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateWashOrdersComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('wash_type', 'basic')
            ->call('save');

        $washOrder = WashOrder::first();
        $this->assertNotNull($washOrder->queued_at);
    }

    public function test_wash_order_with_notes_saves_correctly(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateWashOrdersComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('wash_type', 'premium')
            ->set('customer_notes', 'Please focus on the interior.')
            ->call('save');

        $this->assertDatabaseHas('wash_orders', [
            'notes' => 'Please focus on the interior.',
        ]);
    }

    // ─── Validation Errors ──────────────────────────────────────────────────

    public function test_save_requires_customer_id(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateWashOrdersComponent::class)
            ->set('vehicle_id', $this->vehicle->id)
            ->call('save')
            ->assertHasErrors(['customer_id']);
    }

    public function test_save_requires_vehicle_id(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateWashOrdersComponent::class)
            ->set('customer_id', $this->customer->id)
            ->call('save')
            ->assertHasErrors(['vehicle_id']);
    }

    public function test_save_requires_wash_type(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateWashOrdersComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('wash_type', '')
            ->call('save')
            ->assertHasErrors(['wash_type']);
    }

    public function test_invalid_wash_type_is_rejected(): void
    {
        $this->actingAsUser();

        Livewire::test(CreateWashOrdersComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('wash_type', 'turbo_wash') // not in enum
            ->call('save')
            ->assertHasErrors(['wash_type']);
    }

    // ─── No active branch guard ──────────────────────────────────────────────

    public function test_creation_is_blocked_without_active_branch_session(): void
    {
        $this->actingAs($this->user);
        // Intentionally do NOT set current_branch_id in session

        Livewire::test(CreateWashOrdersComponent::class)
            ->set('customer_id', $this->customer->id)
            ->set('vehicle_id', $this->vehicle->id)
            ->set('wash_type', 'basic')
            ->call('save');

        $this->assertDatabaseCount('wash_orders', 0);
    }

    // ─── Combo flow ──────────────────────────────────────────────────────────

    public function test_work_order_markReady_creates_wash_order_when_combo(): void
    {
        $this->actingAsUser();

        $workOrder = WorkOrder::create([
            'branch_id'       => $this->branch->id,
            'customer_id'     => $this->customer->id,
            'vehicle_id'      => $this->vehicle->id,
            'created_by'      => $this->user->id,
            'type'            => 'service',
            'status'          => 'in_progress',
            'priority'        => 'normal',
            'is_combo'        => true,
            'checked_in_at'   => now(),
        ]);

        $workOrder->markReady();

        $this->assertDatabaseHas('work_orders', [
            'id'     => $workOrder->id,
            'status' => 'ready',
        ]);

        $this->assertDatabaseHas('wash_orders', [
            'work_order_id' => $workOrder->id,
            'source'        => 'combo',
            'status'        => 'queued',
        ]);
    }

    public function test_markReady_without_combo_does_not_create_wash_order(): void
    {
        $this->actingAsUser();

        $workOrder = WorkOrder::create([
            'branch_id'     => $this->branch->id,
            'customer_id'   => $this->customer->id,
            'vehicle_id'    => $this->vehicle->id,
            'created_by'    => $this->user->id,
            'type'          => 'service',
            'status'        => 'in_progress',
            'priority'      => 'normal',
            'is_combo'      => false,
            'checked_in_at' => now(),
        ]);

        $workOrder->markReady();

        $this->assertDatabaseCount('wash_orders', 0);
    }
}
