<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Electronic sign-off record for a single asset's closeout.
 *
 * This directly mirrors Facility Grid's "Asset Closeout Signoff" workflow:
 * a project manager requests sign-off from an approver, the approver either
 * approves (producing a tamper-evident signature hash) or rejects with a
 * reason, and the entire flow is append-only and auditable.
 */
class AssetSignoff extends Model
{
    use BelongsToTenant;
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_WITHDRAWN = 'withdrawn';

    public const ROLE_COMMISSIONING_AUTHORITY = 'commissioning_authority';

    public const ROLE_OWNER_REP = 'owner_rep';

    public const ROLE_FACILITY_MANAGER = 'facility_manager';

    public const ROLE_GENERAL_CONTRACTOR = 'general_contractor';

    protected $fillable = [
        'tenant_id', 'project_id', 'asset_id',
        'requested_by', 'signed_by', 'signer_role',
        'status', 'notes', 'rejection_reason',
        'signature_hash', 'signature_image',
        'signature_ip', 'signature_user_agent',
        'requested_at', 'signed_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'signed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Available signer roles for UI dropdowns, ordered by typical project flow.
     *
     * @return array<string, string>
     */
    public static function signerRoles(): array
    {
        return [
            self::ROLE_COMMISSIONING_AUTHORITY => 'Commissioning Authority',
            self::ROLE_GENERAL_CONTRACTOR => 'General Contractor',
            self::ROLE_OWNER_REP => "Owner's Representative",
            self::ROLE_FACILITY_MANAGER => 'Facility Manager',
        ];
    }
}
