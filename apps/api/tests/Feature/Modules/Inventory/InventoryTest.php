<?php

namespace Tests\Feature\Modules\Inventory;

use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Tenants\Models\Tenant;
use App\Modules\Tenants\Models\TenantMembership;
use App\Modules\Yachts\Models\Yacht;
use App\Modules\Yachts\Models\YachtMembership;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private function access(): array
    {
        $membership = TenantMembership::factory()->create();
        $user = $membership->user;
        $yacht = Yacht::create(['tenant_id' => $membership->tenant_id, 'name' => 'Inventory yacht', 'status' => 'active']);
        YachtMembership::create([
            'tenant_id' => $membership->tenant_id, 'tenant_membership_id' => $membership->id,
            'user_id' => $user->id, 'yacht_id' => $yacht->id, 'role' => 'captain', 'status' => 'active',
            'start_date' => today('UTC'),
        ]);

        return [$membership, $user, $yacht, $user->createToken('test', ['*'], now()->addDay())->plainTextToken];
    }

    private function item(string $tenantId, string $yachtId, array $overrides = []): InventoryItem
    {
        $item = new InventoryItem;
        $item->forceFill(array_merge([
            'tenant_id' => $tenantId, 'yacht_id' => $yachtId, 'name' => 'Engine oil',
            'description' => null, 'category' => 'Engine', 'unit' => 'liter',
            'current_quantity' => '0.000', 'minimum_quantity' => '2.000', 'storage_location' => 'Engine room',
        ], $overrides))->save();

        return $item;
    }

    private function api(string $method, string $path, string $token, string $yachtId, array $data = [])
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token)->withHeader('X-Yacht-Id', $yachtId)->json($method, $path, $data);
    }

    public function test_list_and_detail_are_yacht_scoped_and_low_stock_is_derived(): void
    {
        [$tenant, , $yacht, $token] = $this->access();
        $normal = $this->item($tenant->tenant_id, $yacht->id, ['name' => 'Normal oil', 'current_quantity' => '4.000']);
        $low = $this->item($tenant->tenant_id, $yacht->id, ['name' => 'Low oil', 'current_quantity' => '2.000']);
        $otherYacht = Yacht::create(['tenant_id' => $tenant->tenant_id, 'name' => 'Other yacht', 'status' => 'active']);
        $hiddenYacht = $this->item($tenant->tenant_id, $otherYacht->id, ['name' => 'Hidden yacht item']);
        $foreignTenant = Tenant::factory()->create();
        $foreignYacht = Yacht::create(['tenant_id' => $foreignTenant->id, 'name' => 'Foreign yacht', 'status' => 'active']);
        $hiddenTenant = $this->item($foreignTenant->id, $foreignYacht->id, ['name' => 'Hidden tenant item']);

        $this->api('GET', '/api/inventory', $token, $yacht->id)->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $low->id)->assertJsonPath('data.0.is_low_stock', true)
            ->assertJsonPath('data.1.id', $normal->id)->assertJsonPath('data.1.is_low_stock', false)
            ->assertDontSee($hiddenYacht->id)->assertDontSee($hiddenTenant->id);
        $this->api('GET', '/api/inventory?low_stock=1', $token, $yacht->id)->assertOk()->assertJsonCount(1, 'data');
        $this->api('GET', "/api/inventory/{$hiddenYacht->id}", $token, $yacht->id)->assertNotFound();
        $this->api('GET', "/api/inventory/{$hiddenTenant->id}", $token, $yacht->id)->assertNotFound();
    }

    public function test_create_and_metadata_update_ignore_ownership_and_balance_inputs(): void
    {
        [$tenant, , $yacht, $token] = $this->access();
        $otherTenant = Tenant::factory()->create();
        $otherYacht = Yacht::create(['tenant_id' => $otherTenant->id, 'name' => 'Foreign', 'status' => 'active']);
        $created = $this->api('POST', '/api/inventory', $token, $yacht->id, [
            'name' => 'Coolant', 'category' => 'Engine', 'unit' => 'liter', 'minimum_quantity' => '1.250',
            'tenant_id' => $otherTenant->id, 'yacht_id' => $otherYacht->id, 'current_quantity' => '999.000',
        ])->assertCreated()->assertJsonPath('data.current_quantity', '0.000')->assertJsonMissingPath('data.tenant_id');
        $id = $created->json('data.id');
        $this->assertDatabaseHas('inventory_items', ['id' => $id, 'tenant_id' => $tenant->tenant_id, 'yacht_id' => $yacht->id]);
        $this->assertDatabaseMissing('stock_movements', ['inventory_item_id' => $id]);

        $this->api('PATCH', "/api/inventory/{$id}", $token, $yacht->id, [
            'name' => 'Coolant updated', 'tenant_id' => $otherTenant->id, 'yacht_id' => $otherYacht->id, 'current_quantity' => '88.000',
        ])->assertOk()->assertJsonPath('data.name', 'Coolant updated')->assertJsonPath('data.current_quantity', '0.000');
        $this->assertDatabaseHas('inventory_items', ['id' => $id, 'tenant_id' => $tenant->tenant_id, 'yacht_id' => $yacht->id]);
    }

    public function test_in_out_and_signed_adjustment_store_exact_decimal_deltas(): void
    {
        [$tenant, $user, $yacht, $token] = $this->access();
        $item = $this->item($tenant->tenant_id, $yacht->id);
        $base = "/api/inventory/{$item->id}/movements";

        $this->api('POST', $base, $token, $yacht->id, ['type' => 'in', 'quantity' => '1.125', 'reason' => 'Initial count'])
            ->assertCreated()->assertJsonPath('data.quantity', '1.125')->assertJsonPath('data.balance_after', '1.125');
        $this->api('POST', $base, $token, $yacht->id, ['type' => 'in', 'quantity' => '0.005', 'reason' => 'Top up'])
            ->assertCreated()->assertJsonPath('data.balance_after', '1.130');
        $this->api('POST', $base, $token, $yacht->id, ['type' => 'out', 'quantity' => '0.130', 'reason' => 'Issued'])
            ->assertCreated()->assertJsonPath('data.quantity', '-0.130')->assertJsonPath('data.balance_after', '1.000');
        $this->api('POST', $base, $token, $yacht->id, ['type' => 'adjustment', 'quantity' => '-0.250', 'reason' => 'Count correction'])
            ->assertCreated()->assertJsonPath('data.quantity', '-0.250')->assertJsonPath('data.balance_after', '0.750');

        $this->assertSame('0.750', $item->refresh()->current_quantity);
        $this->assertDatabaseCount('stock_movements', 4);
        $detail = $this->api('GET', "/api/inventory/{$item->id}", $token, $yacht->id)->assertOk();
        $detail->assertJsonPath('data.recent_movements.0.reason', 'Count correction')
            ->assertJsonPath('data.recent_movements.0.performed_by', trim($user->first_name.' '.$user->last_name));
    }

    public function test_invalid_or_negative_operations_do_not_change_balance_or_history(): void
    {
        [$tenant, , $yacht, $token] = $this->access();
        $item = $this->item($tenant->tenant_id, $yacht->id, ['current_quantity' => '1.000']);
        $path = "/api/inventory/{$item->id}/movements";
        $this->api('POST', $path, $token, $yacht->id, ['type' => 'out', 'quantity' => '1.001', 'reason' => 'Too much'])
            ->assertUnprocessable()->assertJsonValidationErrors(['quantity']);
        $this->api('POST', $path, $token, $yacht->id, ['type' => 'adjustment', 'quantity' => '-1.001', 'reason' => 'Bad count'])
            ->assertUnprocessable()->assertJsonValidationErrors(['quantity']);
        $this->api('POST', $path, $token, $yacht->id, ['type' => 'in', 'quantity' => '0.0001', 'reason' => 'Too precise'])
            ->assertUnprocessable()->assertJsonValidationErrors(['quantity']);
        $this->assertSame('1.000', $item->refresh()->current_quantity);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_item_balance_cannot_be_changed_directly_and_movement_update_or_delete_is_prohibited(): void
    {
        [$tenant, , $yacht, $token] = $this->access();
        $item = $this->item($tenant->tenant_id, $yacht->id);
        $this->api('PATCH', "/api/inventory/{$item->id}", $token, $yacht->id, ['current_quantity' => '5.000'])
            ->assertOk()->assertJsonPath('data.current_quantity', '0.000');
        $item->forceFill(['current_quantity' => '5.000']);
        try {
            $item->save();
            $this->fail('Direct balance mutation must require a movement.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('StockMovement', $exception->getMessage());
        }

        $this->api('POST', "/api/inventory/{$item->id}/movements", $token, $yacht->id, [
            'type' => 'in', 'quantity' => '1.000', 'reason' => 'Test',
        ])->assertCreated();
        $movement = StockMovement::firstOrFail();
        try {
            $movement->delete();
            $this->fail('Stock movement history must be immutable.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('immutable', $exception->getMessage());
        }
        try {
            $movement->forceFill(['reason' => 'Edited history'])->save();
            $this->fail('Stock movement history must not be edited.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('immutable', $exception->getMessage());
        }
        try {
            $item->delete();
            $this->fail('Inventory items must not be hard-deleted.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('hard deletion', $exception->getMessage());
        }
    }

    public function test_movement_and_balance_are_atomic_if_balance_write_fails(): void
    {
        [$tenant, , $yacht, $token] = $this->access();
        $item = $this->item($tenant->tenant_id, $yacht->id);
        DB::statement("CREATE TRIGGER reject_inventory_balance BEFORE UPDATE ON inventory_items BEGIN SELECT RAISE(ABORT, 'blocked'); END");
        try {
            try {
                $this->withoutExceptionHandling();
                $this->api('POST', "/api/inventory/{$item->id}/movements", $token, $yacht->id, [
                    'type' => 'in', 'quantity' => '2.000', 'reason' => 'Atomicity test',
                ]);
                $this->fail('The trigger should reject the balance update.');
            } catch (QueryException) {
                $this->assertDatabaseCount('stock_movements', 0);
                $this->assertSame('0.000', $item->refresh()->current_quantity);
            }
        } finally {
            DB::statement('DROP TRIGGER IF EXISTS reject_inventory_balance');
        }
    }

    public function test_database_prevents_movement_from_cross_yacht_or_cross_tenant_item(): void
    {
        [$tenant, $user, $yacht] = $this->access();
        $item = $this->item($tenant->tenant_id, $yacht->id);
        $otherYacht = Yacht::create(['tenant_id' => $tenant->tenant_id, 'name' => 'Other', 'status' => 'active']);
        $foreignTenant = Tenant::factory()->create();
        $foreignYacht = Yacht::create(['tenant_id' => $foreignTenant->id, 'name' => 'Foreign', 'status' => 'active']);
        foreach ([[$otherYacht->id, $tenant->tenant_id], [$foreignYacht->id, $tenant->tenant_id]] as [$wrongYacht, $wrongTenant]) {
            try {
                DB::table('stock_movements')->insert([
                    'id' => (string) Str::uuid(), 'tenant_id' => $wrongTenant, 'yacht_id' => $wrongYacht,
                    'inventory_item_id' => $item->id, 'performed_by_user_id' => $user->id,
                    'type' => 'in', 'quantity' => '1.000', 'balance_after' => '1.000',
                    'reason' => 'boundary test', 'created_at' => now(),
                ]);
                $this->fail('Composite item boundary accepted a mismatched StockMovement.');
            } catch (QueryException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_authentication_and_yacht_context_are_required(): void
    {
        $this->getJson('/api/inventory')->assertUnauthorized();
        [, , $yacht, $token] = $this->access();
        YachtMembership::query()->delete();
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->withHeader('X-Yacht-Id', $yacht->id)->getJson('/api/inventory')->assertForbidden();
    }
}
