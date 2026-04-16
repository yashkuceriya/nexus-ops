<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SensorIngestRequest;
use App\Models\SensorReading;
use App\Models\SensorSource;
use App\Rules\BelongsToCurrentTenant;
use App\Services\Sensor\SensorIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SensorController extends Controller
{
    public function __construct(
        private readonly SensorIngestService $sensorIngestService,
    ) {}

    /**
     * List all sensor sources for the tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'asset_id' => ['sometimes', 'integer', new BelongsToCurrentTenant('assets')],
            'sensor_type' => ['sometimes', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $tenantId = $request->user()->tenant_id;

        $query = SensorSource::where('tenant_id', $tenantId)
            ->with(['asset:id,name,asset_tag']);

        if ($request->filled('asset_id')) {
            $query->where('asset_id', (int) $request->input('asset_id'));
        }

        if ($request->filled('sensor_type')) {
            $query->where('sensor_type', $request->input('sensor_type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $paginator = $query->orderBy('name')
            ->paginate(min((int) $request->input('per_page', 20), 100));

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'tenant_id' => $tenantId,
            ],
        ]);
    }

    /**
     * Ingest sensor readings. Uses SensorIngestRequest for validation.
     * Tenant isolation is enforced in SensorIngestService::ingestBatch.
     */
    public function ingest(SensorIngestRequest $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $result = $this->sensorIngestService->ingestBatch(
            $tenantId,
            $request->validated()['readings'],
        );

        $statusCode = empty($result['errors']) ? 200 : 207;

        return response()->json([
            'data' => $result,
            'meta' => [
                'tenant_id' => $tenantId,
                'processed_at' => now()->toIso8601String(),
            ],
        ], $statusCode);
    }

    /**
     * Get recent readings for a sensor source with pagination.
     */
    public function readings(Request $request, int $sensorSourceId): JsonResponse
    {
        $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:500'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
        ]);

        $tenantId = $request->user()->tenant_id;

        $sensorSource = SensorSource::where('tenant_id', $tenantId)
            ->find($sensorSourceId);

        if (! $sensorSource) {
            return response()->json([
                'data' => null,
                'meta' => ['error' => 'Sensor source not found.'],
            ], 404);
        }

        $query = SensorReading::where('sensor_source_id', $sensorSourceId)
            ->orderByDesc('recorded_at');

        if ($request->filled('from')) {
            $query->where('recorded_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('recorded_at', '<=', $request->input('to'));
        }

        $paginator = $query->paginate(
            min((int) $request->input('per_page', 50), 500),
        );

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'sensor_source' => [
                    'id' => $sensorSource->id,
                    'name' => $sensorSource->name,
                    'sensor_type' => $sensorSource->sensor_type,
                    'unit' => $sensorSource->unit,
                ],
                'tenant_id' => $tenantId,
            ],
        ]);
    }
}
