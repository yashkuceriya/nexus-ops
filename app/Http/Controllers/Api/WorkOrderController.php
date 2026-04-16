<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkOrderRequest;
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Models\WorkOrder;
use App\Rules\BelongsToCurrentTenant;
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
            'status' => ['sometimes', 'string', Rule::in(['pending', 'open', 'assigned', 'in_progress', 'on_hold', 'completed', 'verified', 'cancelled'])],
            'priority' => ['sometimes', 'string', Rule::in(['low', 'medium', 'high', 'critical', 'emergency'])],
            'assigned_to' => ['sometimes', 'integer', new BelongsToCurrentTenant('users')],
            'project_id' => ['sometimes', 'integer', new BelongsToCurrentTenant('projects')],
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

        $this->authorize('view', $workOrder);

        return response()->json([
            'data' => $workOrder,
            'meta' => ['tenant_id' => $tenantId],
        ]);
    }

    /**
     * Create a new work order. Uses StoreWorkOrderRequest with tenant-scoped
     * validation and WorkOrderPolicy::create authorization.
     */
    public function store(StoreWorkOrderRequest $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $workOrder = $this->workOrderService->create(
            tenantId: $tenantId,
            createdBy: $request->user()->id,
            data: $request->validated(),
        );

        return response()->json([
            'data' => $workOrder->load(['project:id,name', 'assignee:id,name']),
            'meta' => ['tenant_id' => $tenantId],
        ], 201);
    }

    /**
     * Update an existing work order. Uses UpdateWorkOrderRequest with tenant-scoped
     * validation and WorkOrderPolicy::update authorization.
     */
    public function update(UpdateWorkOrderRequest $request, int $id): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        $workOrder = $this->workOrderService->update($tenantId, $id, $request->validated());

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
            'status' => ['required', 'string', Rule::in(['open', 'assigned', 'in_progress', 'on_hold', 'completed', 'verified', 'cancelled'])],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $tenantId = $request->user()->tenant_id;
        $workOrder = WorkOrder::where('tenant_id', $tenantId)->find($id);

        if (! $workOrder) {
            return response()->json([
                'data' => null,
                'meta' => ['error' => 'Work order not found.'],
            ], 404);
        }

        $this->authorize('transitionStatus', $workOrder);

        $workOrder = $this->workOrderService->transitionStatus(
            tenantId: $tenantId,
            id: $id,
            newStatus: $validated['status'],
            notes: $validated['resolution_notes'] ?? null,
        );

        if (! $workOrder) {
            return response()->json([
                'data' => null,
                'meta' => ['error' => 'Invalid status transition.'],
            ], 422);
        }

        return response()->json([
            'data' => $workOrder,
            'meta' => ['tenant_id' => $tenantId],
        ]);
    }
}
