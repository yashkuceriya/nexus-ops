<?php

declare(strict_types=1);

namespace App\Services\Signoff;

use App\Models\Asset;
use App\Models\AssetSignoff;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Orchestrates the Electronic Asset Signoff workflow.
 *
 * The service is intentionally narrow — each method is a discrete, auditable
 * state transition (request → approve / reject / withdraw) so that audit logs
 * tell a clean story of who did what and when.
 */
final class AssetSignoffService
{
    /**
     * Create a pending sign-off request for an asset. Multiple concurrent
     * sign-offs with different signer roles are allowed (e.g. commissioning
     * authority + owner rep both signing off the same asset).
     */
    public function request(
        Asset $asset,
        User $requester,
        string $signerRole,
        ?string $notes = null,
        ?int $expiresInDays = 14,
    ): AssetSignoff {
        if (! array_key_exists($signerRole, AssetSignoff::signerRoles())) {
            throw new InvalidArgumentException("Unknown signer role: {$signerRole}");
        }

        if ($asset->tenant_id !== $requester->tenant_id) {
            throw new InvalidArgumentException('Requester and asset must belong to the same tenant.');
        }

        return DB::transaction(function () use ($asset, $requester, $signerRole, $notes, $expiresInDays): AssetSignoff {
            $signoff = AssetSignoff::create([
                'tenant_id' => $asset->tenant_id,
                'project_id' => $asset->project_id,
                'asset_id' => $asset->id,
                'requested_by' => $requester->id,
                'signer_role' => $signerRole,
                'status' => AssetSignoff::STATUS_PENDING,
                'notes' => $notes,
                'requested_at' => Carbon::now(),
                'expires_at' => $expiresInDays > 0
                    ? Carbon::now()->addDays($expiresInDays)
                    : null,
            ]);

            AuditLog::record(
                action: 'asset_signoff_requested',
                model: $signoff,
                newValues: [
                    'asset_id' => $asset->id,
                    'asset_name' => $asset->name,
                    'signer_role' => $signerRole,
                    'expires_at' => $signoff->expires_at?->toIso8601String(),
                ],
            );

            return $signoff;
        });
    }

    /**
     * Approve (sign) a pending signoff. Produces a tamper-evident hash over
     * the approval payload so the signature can later be independently
     * validated even if a DB row is edited.
     */
    public function approve(
        AssetSignoff $signoff,
        User $signer,
        ?string $signatureImage = null,
        ?string $notes = null,
        ?Request $request = null,
    ): AssetSignoff {
        if ($signoff->tenant_id !== $signer->tenant_id) {
            throw new InvalidArgumentException('Signer and signoff must belong to the same tenant.');
        }

        if (! $signoff->isPending()) {
            throw new InvalidArgumentException('Only pending signoffs can be approved.');
        }

        if ($signoff->isExpired()) {
            throw new InvalidArgumentException('This signoff request has expired.');
        }

        return DB::transaction(function () use ($signoff, $signer, $signatureImage, $notes, $request): AssetSignoff {
            $signedAt = Carbon::now();

            $hashInput = implode('|', [
                $signoff->id,
                $signoff->asset_id,
                $signoff->signer_role,
                $signer->id,
                $signer->email,
                $signedAt->toIso8601String(),
            ]);

            $signoff->update([
                'status' => AssetSignoff::STATUS_APPROVED,
                'signed_by' => $signer->id,
                'signed_at' => $signedAt,
                'notes' => $notes ?? $signoff->notes,
                'signature_hash' => hash('sha256', $hashInput),
                'signature_image' => $signatureImage,
                'signature_ip' => $request?->ip(),
                'signature_user_agent' => $request?->userAgent()
                    ? substr($request->userAgent(), 0, 500)
                    : null,
            ]);

            AuditLog::record(
                action: 'asset_signoff_approved',
                model: $signoff->refresh(),
                newValues: [
                    'signer_id' => $signer->id,
                    'signer_role' => $signoff->signer_role,
                    'signature_hash' => $signoff->signature_hash,
                    'signed_at' => $signedAt->toIso8601String(),
                ],
            );

            return $signoff;
        });
    }

    /**
     * Reject a pending signoff with a required reason.
     */
    public function reject(AssetSignoff $signoff, User $signer, string $reason): AssetSignoff
    {
        if ($signoff->tenant_id !== $signer->tenant_id) {
            throw new InvalidArgumentException('Signer and signoff must belong to the same tenant.');
        }

        if (! $signoff->isPending()) {
            throw new InvalidArgumentException('Only pending signoffs can be rejected.');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new InvalidArgumentException('A rejection reason is required.');
        }

        return DB::transaction(function () use ($signoff, $signer, $reason): AssetSignoff {
            $signoff->update([
                'status' => AssetSignoff::STATUS_REJECTED,
                'signed_by' => $signer->id,
                'signed_at' => Carbon::now(),
                'rejection_reason' => $reason,
            ]);

            AuditLog::record(
                action: 'asset_signoff_rejected',
                model: $signoff->refresh(),
                newValues: [
                    'signer_id' => $signer->id,
                    'reason' => $reason,
                ],
            );

            return $signoff;
        });
    }

    /**
     * Withdraw a pending signoff (only the original requester or an admin).
     */
    public function withdraw(AssetSignoff $signoff, User $user): AssetSignoff
    {
        if ($signoff->tenant_id !== $user->tenant_id) {
            throw new InvalidArgumentException('User and signoff must belong to the same tenant.');
        }

        if (! $signoff->isPending()) {
            throw new InvalidArgumentException('Only pending signoffs can be withdrawn.');
        }

        $canWithdraw = $signoff->requested_by === $user->id || $user->isAdmin();

        if (! $canWithdraw) {
            throw new InvalidArgumentException('Only the original requester or an admin can withdraw this signoff.');
        }

        return DB::transaction(function () use ($signoff, $user): AssetSignoff {
            $signoff->update([
                'status' => AssetSignoff::STATUS_WITHDRAWN,
                'signed_by' => null,
                'signed_at' => Carbon::now(),
            ]);

            AuditLog::record(
                action: 'asset_signoff_withdrawn',
                model: $signoff->refresh(),
                newValues: ['withdrawn_by' => $user->id],
            );

            return $signoff;
        });
    }

    /**
     * Verify the signature hash matches the stored payload. Returns false
     * if any of the hash inputs have been tampered with since signing.
     */
    public function verify(AssetSignoff $signoff): bool
    {
        if (! $signoff->isApproved() || ! $signoff->signature_hash || ! $signoff->signed_at || ! $signoff->signer) {
            return false;
        }

        $expected = hash('sha256', implode('|', [
            $signoff->id,
            $signoff->asset_id,
            $signoff->signer_role,
            $signoff->signed_by,
            $signoff->signer->email,
            $signoff->signed_at->toIso8601String(),
        ]));

        return hash_equals($expected, $signoff->signature_hash);
    }
}
