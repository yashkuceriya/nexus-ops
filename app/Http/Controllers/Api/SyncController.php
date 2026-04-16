<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncFacilityGridData;
use App\Models\SyncWatermark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

final class SyncController extends Controller
{
    /**
     * Manually trigger a External system sync for the authenticated user's tenant.
     */
    public function triggerSync(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        if (! $user->isManager()) {
            return response()->json([
                'data' => null,
                'meta' => ['error' => 'Only managers and above can trigger a sync.'],
            ], 403);
        }

        $tenant = $user->tenant;

        if (! $tenant->facilitygrid_api_url || ! $tenant->facilitygrid_api_token) {
            return response()->json([
                'data' => null,
                'meta' => ['error' => 'External system integration is not configured for this tenant.'],
            ], 422);
        }

        if ($tenant->isTokenExpired()) {
            return response()->json([
                'data' => null,
                'meta' => ['error' => 'External system API token has expired. Please re-authenticate.'],
            ], 422);
        }

        // Check for circuit-broken watermarks
        $brokenWatermarks = SyncWatermark::where('tenant_id', $tenantId)
            ->get()
            ->filter(fn (SyncWatermark $wm) => $wm->isCircuitBroken());

        if ($brokenWatermarks->isNotEmpty()) {
            return response()->json([
                'data' => [
                    'circuit_broken_entities' => $brokenWatermarks->pluck('entity')->all(),
                ],
                'meta' => ['error' => 'Sync circuit breaker is open for some entities. Resolve errors before retrying.'],
            ], 422);
        }

        // Dispatch the sync job. The job constructor expects the Tenant model
        // (serialized via SerializesModels) so Bus can reconstruct it on the
        // worker. Passing an int here previously caused a TypeError.
        if (class_exists(SyncFacilityGridData::class)) {
            Bus::dispatch(new SyncFacilityGridData($tenant));
        }

        // Mark watermarks as attempted
        SyncWatermark::where('tenant_id', $tenantId)
            ->update(['last_attempted_at' => now()]);

        return response()->json([
            'data' => [
                'message' => 'Sync has been queued.',
                'tenant_id' => $tenantId,
            ],
            'meta' => [
                'queued_at' => now()->toIso8601String(),
            ],
        ], 202);
    }

    /**
     * Show sync watermark status for the tenant.
     */
    public function status(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $watermarks = SyncWatermark::where('tenant_id', $tenantId)
            ->orderBy('entity')
            ->get()
            ->map(fn (SyncWatermark $wm) => [
                'id' => $wm->id,
                'connector' => $wm->connector,
                'entity' => $wm->entity,
                'cursor' => $wm->cursor,
                'last_successful_sync_at' => $wm->last_successful_sync_at?->toIso8601String(),
                'last_attempted_at' => $wm->last_attempted_at?->toIso8601String(),
                'last_error' => $wm->last_error,
                'consecutive_failures' => $wm->consecutive_failures,
                'circuit_broken' => $wm->isCircuitBroken(),
            ]);

        return response()->json([
            'data' => $watermarks,
            'meta' => [
                'tenant_id' => $tenantId,
                'total' => $watermarks->count(),
            ],
        ]);
    }
}
