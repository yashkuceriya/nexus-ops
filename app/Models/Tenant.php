<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'domain', 'settings', 'is_active',
        'external_api_url', 'external_api_token', 'external_auth_type',
        'external_token_expires_at', 'external_refresh_token', 'external_scopes',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'external_scopes' => 'array',
            'external_api_token' => 'encrypted',
            'external_refresh_token' => 'encrypted',
            'external_token_expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function vendorContracts(): HasMany
    {
        return $this->hasMany(VendorContract::class);
    }

    public function statusMappings(): HasMany
    {
        return $this->hasMany(StatusMapping::class);
    }

    public function syncWatermarks(): HasMany
    {
        return $this->hasMany(SyncWatermark::class);
    }

    public function automationRules(): HasMany
    {
        return $this->hasMany(AutomationRule::class);
    }

    public function isTokenExpired(): bool
    {
        if (! $this->external_token_expires_at) {
            return false;
        }

        return $this->external_token_expires_at->isPast();
    }
}
