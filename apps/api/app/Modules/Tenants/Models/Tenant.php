<?php

namespace App\Modules\Tenants\Models;

use App\Modules\Crew\Models\CrewMember;
use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Maintenance\Models\MaintenanceTask;
use App\Modules\Yachts\Models\Yacht;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'slug', 'type', 'country', 'timezone', 'currency'];

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(TenantMembership::class);
    }

    public function yachts(): HasMany
    {
        return $this->hasMany(Yacht::class);
    }

    public function crewMembers(): HasMany
    {
        return $this->hasMany(CrewMember::class);
    }

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
