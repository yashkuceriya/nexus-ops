<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class AssetController extends Controller
{
    /**
     * List assets with optional filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => ['sometimes', 'integer', 'exists:projects,id'],
            'system_type' => ['sometimes', 'string', 'max:100'],
            'condition' => ['sometimes', 'string', Rule::in(['excellent', 'good', 'fair', 'poor', 'critical'])],
            'category' => ['sometimes', 'string', 'max:100'],
            'commissioning_status' => ['sometimes', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $tenantId = $request->user()->tenant_id;

        $query = Asset::where('tenant_id', $tenantId)
            ->with(['project:id,name', 'location:id,name,type']);

        if ($request->filled('project_id')) {
            $query->where('project_id', (int) $request->input('project_id'));
        }

        if ($request->filled('system_type')) {
            $query->where('system_type', $request->input('system_type'));
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->input('condition'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('commissioning_status')) {
            $query->where('commissioning_status', $request->input('commissioning_status'));
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
     * Show a single asset with maintenance schedules, sensor data, and work orders.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $asset = Asset::where('tenant_id', $tenantId)
            ->with([
                'project:id,name',
                'location:id,name,type',
                'maintenanceSchedules' => fn ($q) => $q->where('is_active', true)->orderBy('next_due_date'),
                'sensorSources' => fn ($q) => $q->where('is_active', true),
                'workOrders' => fn ($q) => $q->latest()->limit(10),
                'workOrders.assignee:id,name',
            ])
            ->find($id);

        if (! $asset) {
            return response()->json([
                'data' => null,
                'meta' => ['error' => 'Asset not found.'],
            ], 404);
        }

        return response()->json([
            'data' => $asset,
            'meta' => [
                'tenant_id' => $tenantId,
                'warranty_active' => $asset->isWarrantyActive(),
            ],
        ]);
    }

    /**
     * Find an asset by its QR code.
     */
    public function qrLookup(Request $request, string $code): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $asset = Asset::where('tenant_id', $tenantId)
            ->where('qr_code', $code)
            ->with([
                'project:id,name',
                'location:id,name,type',
                'maintenanceSchedules' => fn ($q) => $q->where('is_active', true),
                'sensorSources' => fn ($q) => $q->where('is_active', true),
            ])
            ->first();

        if (! $asset) {
            return response()->json([
                'data' => null,
                'meta' => ['error' => 'Asset not found for the given QR code.'],
            ], 404);
        }

        return response()->json([
            'data' => $asset,
            'meta' => [
                'tenant_id' => $tenantId,
                'warranty_active' => $asset->isWarrantyActive(),
            ],
        ]);
    }
}
