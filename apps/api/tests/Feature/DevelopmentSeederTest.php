<?php

namespace Tests\Feature;

use App\Modules\Crew\Models\CrewMember;
use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Support\StockQuantity;
use App\Modules\Maintenance\Models\MaintenanceTask;
use App\Modules\Tenants\Models\TenantMembership;
use App\Modules\Users\Models\User;
use App\Modules\Yachts\Models\Yacht;
use App\Modules\Yachts\Models\YachtMembership;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DevelopmentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_development_fixture_is_idempotent_and_can_authenticate_with_a_tenant(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseCount('tenant_memberships', 1);
        $this->assertDatabaseCount('yachts', 1);
        $this->assertDatabaseCount('yacht_memberships', 1);
        $this->assertDatabaseCount('crew_members', 5);
        $this->assertDatabaseCount('maintenance_tasks', 6);
        $this->assertDatabaseCount('maintenance_assignments', 8);
        $this->assertDatabaseCount('inventory_items', 8);
        $this->assertDatabaseCount('stock_movements', 11);
        foreach (InventoryItem::with('movements')->get() as $item) {
            $movementTotal = $item->movements->sum(fn (StockMovement $movement) => StockQuantity::milli((string) $movement->quantity));
            $this->assertSame(StockQuantity::milli((string) $item->current_quantity), $movementTotal, $item->name.' balance matches its movement history');
        }
        $this->assertTrue(Hash::check('YachtOS-Dev-2026!', User::sole()->password));
        $this->assertSame(1, TenantMembership::currentlyActive()->count());

        $token = $this->postJson('/api/auth/login', [
            'email' => 'captain@azureyachting.com', 'password' => 'YachtOS-Dev-2026!',
        ])->assertOk()->json('data.access_token');

        $this->withToken($token)->getJson('/api/tenant')->assertOk()
            ->assertJsonPath('data.name', 'Azure Yachting');
        $this->withToken($token)->getJson('/api/yacht')->assertOk()->assertJsonPath('data.name', 'M/Y Azure');
        $this->assertSame(1, Yacht::count());
        $this->assertSame(1, YachtMembership::count());
        $this->withToken($token)->getJson('/api/crew')->assertOk()->assertJsonCount(5, 'data')
            ->assertJsonFragment(['position' => 'Captain']);
        $this->assertSame(5, CrewMember::count());
        $this->withToken($token)->getJson('/api/maintenance')->assertOk()->assertJsonCount(6, 'data')
            ->assertJsonFragment(['status' => 'in_progress'])->assertJsonFragment(['status' => 'completed']);
        $this->assertSame(6, MaintenanceTask::count());
        $this->assertSame(8, InventoryItem::count());
        $this->withToken($token)->getJson('/api/inventory')->assertOk()->assertJsonCount(8, 'data')
            ->assertJsonFragment(['name' => 'Raw-water impeller', 'current_quantity' => '0.000', 'is_low_stock' => true]);
    }

    public function test_seeder_never_creates_development_credentials_in_production(): void
    {
        $this->app->instance('env', 'production');
        $this->app->make(DatabaseSeeder::class)->run();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('tenants', 0);
        $this->assertDatabaseCount('tenant_memberships', 0);
        $this->assertDatabaseCount('yachts', 0);
        $this->assertDatabaseCount('yacht_memberships', 0);
        $this->assertDatabaseCount('crew_members', 0);
        $this->assertDatabaseCount('maintenance_tasks', 0);
        $this->assertDatabaseCount('maintenance_assignments', 0);
        $this->assertDatabaseCount('inventory_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }
}
