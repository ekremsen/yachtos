<?php

namespace App\Modules\Tenants\Models;

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
}
