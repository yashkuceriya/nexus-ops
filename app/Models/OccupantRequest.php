<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OccupantRequest extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'work_order_id', 'tracking_token', 'requester_name',
        'requester_email', 'requester_phone', 'project_id', 'location_id',
        'category', 'description', 'photo_path', 'priority_suggestion',
        'status', 'satisfaction_rating', 'satisfaction_comment',
        'acknowledged_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
            'satisfaction_rating' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public static function generateTrackingToken(): string
    {
        do {
            $token = strtoupper(Str::random(8));
        } while (static::where('tracking_token', $token)->exists());

        return $token;
    }
}
