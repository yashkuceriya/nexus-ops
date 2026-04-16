<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkOrderRequest;
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Services\WorkOrder\WorkOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class WorkOrderController extends Controller
{
    public function __construct(
        private readonly WorkOrderService $workOrderService,
    ) {}

    /**
     * List work orders with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['open', 'in_progress', 'on_hold', 'completed', 'verified', 'cancelled'])],
            'priority' => ['sometimes', 'string', Rule::in(['low', 'medium', 'high', 'critical'])],
            'assigned_to' => ['sometimes', 'integer', 'exists:users,id'],
            'project_id' => ['sometimes', 'integer', 'exists:projects,id'],
            'type' => ['sometimes', 'string'],
            'sla_breached' => ['sometimes', 'boolean'],
            'sort_by' => ['sometimes', 'string', Rule::in(['created_at', 'updated_at', 'sla_deadline', 'priority', 'status'])],
            'sort_dir' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $tenantId = $request->user()->tenant_id;
        $paginator = $this->workOrderService->list($tenantId, $request->all());

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
     * Show a single work order with all relations.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $workOrder = $this->workOrderService->find($tenantId, $id);

        if (! $workOrder) {
            return response()->json([
                'data' => null,
                'meta' => ['error' => 'Work order not found.'],
            ], 404);
        }

        return response()->json([
            'data' => $workOrder,
            'meta' => ['tenant_id' => $tenantId],
        ]);
    }

    /**
     * Create a new work order.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', 'string', Rule::in(['low', 'medium', 'high', 'critical'])],
            'type' => ['required', 'string', Rule::in(['corrective', 'preventive', 'inspection', 'emergency'])],
            'asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'issue_id' => ['nullable', 'integer', 'exists:issues,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'sla_hours' => ['nullable', 'integer', 'min:1'],
            'sla_deadline' => ['nullable', 'date', 'after:now'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'source' => ['nullable', 'string', Rule::in(['manual', 'sensor', 'inspection', 'sync'])],
        ]);

        $tenantId = $request->user()->tenant_id;
        $workOrder = $this->workOrderService->create(
            tenantId: $tenantId,
            createdBy: $request->user()->id,
            data: $validated,
        );

        return response()->json([
            'data' => $workOrder->load(['project:id,name', 'assignee:id,name']),
            'meta' => ['tenant_id' => $tenantId],
        ], 201);
    }

    /**
     * Update an existing work order.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['sometimes', 'string', Rule::in(['low', 'medium', 'high', 'critical'])],
            'type' => ['sometimes', 'string', Rule::in(['corrective', 'preventive', 'inspection', 'emergency'])],
            'asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'sla_hours' => ['nullable', 'integer', 'min:1'],
            'sla_deadline' => ['nullable', 'date'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $tenantId = $request->user()->tenant_id;
        $workOrder = $this->workOrderService->update($tenantId, $id, $validated);

        if (! $workOrder) {
            return response()->json([
                'data' => null,
                'meta' => ['error' => 'Work order not found.'],
            ], 404);
        }

        return response()->json([
            'data' => $workOrder,
            'meta' => ['tenant_id' => $tenantId],
        ]);
    }

    /**
     * Transition a work order's status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['open', 'in_progress', 'on_hold', 'completed', 'verified', 'cancelled'])],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $tenantId = $request->user()->tenant_id;
        $workOrder = $this->workOrderService->transitionStatus(
            tenantId: $tenantId,
            id: $id,
            newStatus: $validated['status'],
            notes: $validated['resolution_notes'] ?? null,
        );

        if (! $workOrder) {
            return response()->json([
                'data' => null,
                'meta' => ['error' => 'Work order not found or invalid status transition.'],
            ], 422);
        }

        return response()->json([
            'data' => $workOrder,
            'meta' => ['tenant_id' => $tenantId],
        ]);
    }
}
