<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'name', 'contact_name', 'email', 'phone',
        'address', 'city', 'state', 'zip',
        'trade_specialties', 'insurance_expiry', 'license_number', 'is_active',
        'avg_response_hours', 'avg_completion_hours',
        'total_work_orders', 'total_spend', 'rating', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'trade_specialties' => 'array',
            'insurance_expiry' => 'date',
            'is_active' => 'boolean',
            'avg_response_hours' => 'decimal:2',
            'avg_completion_hours' => 'decimal:2',
            'total_spend' => 'decimal:2',
            'rating' => 'decimal:1',
        ];
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(VendorContract::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function getInsuranceStatus(): string
    {
        if (! $this->insurance_expiry) {
            return 'unknown';
        }

        if ($this->insurance_expiry->isPast()) {
            return 'expired';
        }

        if ($this->insurance_expiry->diffInDays(now()) <= 30) {
            return 'expiring';
        }

        return 'active';
    }

    public function getActiveContract(): ?VendorContract
    {
        return $this->contracts()->where('status', 'active')->first();
    }
}
