<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorContract extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'vendor_id', 'tenant_id', 'title', 'contract_number',
        'start_date', 'end_date', 'auto_renew',
        'monthly_cost', 'annual_value', 'nte_limit',
        'scope', 'terms', 'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'auto_renew' => 'boolean',
            'monthly_cost' => 'decimal:2',
            'annual_value' => 'decimal:2',
            'nte_limit' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return !$this->isExpired() && $this->end_date->diffInDays(now()) <= 30;
    }
}
