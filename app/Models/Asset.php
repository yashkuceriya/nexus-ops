<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'project_id', 'location_id', 'parent_asset_id', 'external_asset_id',
        'name', 'asset_tag', 'qr_code', 'category', 'system_type',
        'manufacturer', 'model_number', 'serial_number',
        'condition', 'commissioning_status',
        'install_date', 'warranty_expiry', 'replacement_cost',
        'expected_life_years', 'runtime_hours', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'install_date' => 'date',
            'warranty_expiry' => 'date',
            'replacement_cost' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'parent_asset_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Asset::class, 'parent_asset_id');
    }

    /**
     * Returns true if this asset is a top-level system (has child components).
     */
    public function isSystem(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Returns true if this asset is a sub-component (has a parent).
     */
    public function isComponent(): bool
    {
        return $this->parent_asset_id !== null;
    }

    /**
     * Returns array of ancestors from immediate parent up to the root.
     */
    public function hierarchyPath(): array
    {
        $ancestors = [];
        $current = $this->parent;

        while ($current) {
            $ancestors[] = $current;
            $current = $current->parent;
        }

        return $ancestors;
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function sensorSources(): HasMany
    {
        return $this->hasMany(SensorSource::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function closeoutRequirements(): HasMany
    {
        return $this->hasMany(CloseoutRequirement::class);
    }

    public function testExecutions(): HasMany
    {
        return $this->hasMany(TestExecution::class);
    }

    public function isWarrantyActive(): bool
    {
        return $this->warranty_expiry && $this->warranty_expiry->isFuture();
    }

    public function generateQrCode(): string
    {
        return 'NXO-'.str_pad($this->id, 8, '0', STR_PAD_LEFT);
    }
}
