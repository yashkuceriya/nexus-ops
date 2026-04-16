<?php

declare(strict_types=1);

namespace App\Services\WorkOrder;

use App\Domain\Priority;
use App\Domain\WorkOrderStatus;
use App\Events\WorkOrderStatusChanged;
use App\Models\AuditLog;
use App\Models\Issue;
use App\Models\MaintenanceSchedule;
use App\Models\SensorSource;
use App\Models\StatusMapping;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class WorkOrderService
{
    // ────────────────────────────────────────────────────────────────
    // Creation methods
    // ────────────────────────────────────────────────────────────────

    /**
     * Create a work order from an imported issue, mapping status via StatusMapping.
     */
    public function createFromIssue(Issue $issue): WorkOrder
    {
        return DB::transaction(function () use ($issue): WorkOrder {
            $mappedStatus = StatusMapping::resolve(
                tenantId: $issue->tenant_id,
                sourceSystem: $issue->source_system ?? 'facilitygrid',
                sourceEntity: 'issue',
                sourceStatus: $issue->status,
                targetEntity: 'work_order',
            ) ?? 'open';

            $priority = $issue->priority ?? 'medium';
            $slaHours = Priority::tryFrom($priority)?->slaHours() ?? Priority::Medium->slaHours();

            $workOrder = WorkOrder::create([
                'tenant_id'    => $issue->tenant_id,
                'project_id'   => $issue->project_id,
                'asset_id'     => $issue->asset_id,
                'issue_id'     => $issue->id,
                'wo_number'    => WorkOrder::generateWoNumber(),
                'title'        => $issue->title,
                'description'  => $issue->description,
                'status'       => $mappedStatus,
                'priority'     => $priority,
                'type'         => 'corrective',
                'source'       => 'facilitygrid',
                'sla_hours'    => $slaHours,
                'sla_deadline' => Carbon::now()->addHours($slaHours),
            ]);

            AuditLog::record(
                action: 'work_order_created',
                model: $workOrder,
                newValues: [
                    'source'    => 'issue',
                    'issue_id'  => $issue->id,
                    'priority'  => $priority,
                    'sla_hours' => $slaHours,
                ],
                source: 'system',
            );

            return $workOrder;
        });
    }

    /**
     * Create an emergency work order from a sensor threshold breach.
     */
    public function createFromSensorAlert(SensorSource $sensor, float $value): WorkOrder
    {
        return DB::transaction(function () use ($sensor, $value): WorkOrder {
            $anomalyType = $sensor->getAnomalyType($value) ?? 'threshold_breach';
            $priority = 'emergency';
            $slaHours = Priority::Emergency->slaHours();

            $workOrder = WorkOrder::create([
                'tenant_id'    => $sensor->tenant_id,
                'asset_id'     => $sensor->asset_id,
                'location_id'  => $sensor->location_id,
                'wo_number'    => WorkOrder::generateWoNumber(),
                'title'        => "Sensor Alert: {$sensor->name} - {$anomalyType}",
                'description'  => sprintf(
                    'Automated alert: Sensor "%s" (type: %s) recorded value %.2f %s, which is %s. Thresholds: min=%s, max=%s.',
                    $sensor->name,
                    $sensor->sensor_type,
                    $value,
                    $sensor->unit ?? '',
                    $anomalyType === 'below_minimum' ? 'below minimum threshold' : 'above maximum threshold',
                    $sensor->threshold_min ?? 'N/A',
                    $sensor->threshold_max ?? 'N/A',
                ),
                'status'       => 'open',
                'priority'     => $priority,
                'type'         => 'emergency',
                'source'       => 'sensor',
                'sla_hours'    => $slaHours,
                'sla_deadline' => Carbon::now()->addHours($slaHours),
            ]);

            AuditLog::record(
                action: 'work_order_created',
                model: $workOrder,
                newValues: [
                    'source'       => 'sensor',
                    'sensor_id'    => $sensor->id,
                    'sensor_value' => $value,
                    'anomaly_type' => $anomalyType,
                    'priority'     => $priority,
                    'sla_hours'    => $slaHours,
                ],
                source: 'system',
            );

            return $workOrder;
        });
    }

    /**
     * Create a preventive maintenance work order from a schedule.
     */
    public function createFromSchedule(MaintenanceSchedule $schedule): WorkOrder
    {
        return DB::transaction(function () use ($schedule): WorkOrder {
            $priority = 'medium';
            $slaHours = Priority::Medium->slaHours();

            $workOrder = WorkOrder::create([
                'tenant_id'    => $schedule->tenant_id,
                'asset_id'     => $schedule->asset_id,
                'wo_number'    => WorkOrder::generateWoNumber(),
                'title'        => "PM: {$schedule->name}",
                'description'  => $schedule->description,
                'status'       => 'open',
                'priority'     => $priority,
                'type'         => 'preventive',
                'source'       => 'schedule',
                'sla_hours'    => $slaHours,
                'sla_deadline' => Carbon::now()->addHours($slaHours),
            ]);

            AuditLog::record(
                action: 'work_order_created',
                model: $workOrder,
                newValues: [
                    'source'      => 'schedule',
                    'schedule_id' => $schedule->id,
                    'frequency'   => $schedule->frequency,
                    'priority'    => $priority,
                    'sla_hours'   => $slaHours,
                ],
                source: 'system',
            );

            return $workOrder;
        });
    }

    // ────────────────────────────────────────────────────────────────
    // Status & assignment
    // ────────────────────────────────────────────────────────────────

    /**
     * Transition a work order to a new status with validation.
     *
     * @throws InvalidArgumentException when the transition is not allowed
     */
    public function updateStatus(WorkOrder $workOrder, string $newStatus, ?string $notes = null): WorkOrder
    {
        $currentStatus = $workOrder->status;

        $current = WorkOrderStatus::tryFrom($currentStatus);
        $target = WorkOrderStatus::tryFrom($newStatus);

        if (! $target) {
            throw new InvalidArgumentException("Invalid status: {$newStatus}");
        }

        if (! $current || ! $current->canTransitionTo($target)) {
            $allowed = $current
                ? implode(', ', array_map(fn (WorkOrderStatus $s) => $s->value, $current->allowedTransitions()))
                : '';
            throw new InvalidArgumentException(
                "Cannot transition from '{$currentStatus}' to '{$newStatus}'. Allowed: {$allowed}",
            );
        }

        return DB::transaction(function () use ($workOrder, $currentStatus, $newStatus, $notes): WorkOrder {
            $updates = ['status' => $newStatus];

            if ($newStatus === 'in_progress' && ! $workOrder->started_at) {
                $updates['started_at'] = Carbon::now();
            }

            if ($newStatus === 'completed') {
                $updates['completed_at'] = Carbon::now();
                $updates['sla_breached'] = $workOrder->isSlaBreached();
            }

            if ($newStatus === 'verified') {
                $updates['verified_at'] = Carbon::now();
            }

            if ($notes !== null) {
                $updates['resolution_notes'] = $notes;
            }

            $workOrder->update($updates);
            $workOrder->refresh();

            AuditLog::record(
                action: 'work_order_status_changed',
                model: $workOrder,
                oldValues: ['status' => $currentStatus],
                newValues: ['status' => $newStatus, 'notes' => $notes],
            );

            WorkOrderStatusChanged::dispatch(
                tenantId: $workOrder->tenant_id,
                workOrderId: $workOrder->id,
                oldStatus: $currentStatus,
                newStatus: $newStatus,
                updatedBy: auth()->user()?->name,
            );

            return $workOrder;
        });
    }

    /**
     * Assign a work order to a user with SLA calculation.
     */
    public function assignWorkOrder(WorkOrder $workOrder, User $user): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $user): WorkOrder {
            $previousAssignee = $workOrder->assigned_to;

            $updates = [
                'assigned_to' => $user->id,
            ];

            // Auto-transition to assigned if currently open
            if ($workOrder->status === 'open') {
                $updates['status'] = 'assigned';
            }

            // Recalculate SLA deadline from assignment time if work has not started
            if (! $workOrder->started_at) {
                $slaHours = Priority::tryFrom($workOrder->priority)?->slaHours() ?? Priority::Medium->slaHours();
                $updates['sla_hours'] = $slaHours;
                $updates['sla_deadline'] = Carbon::now()->addHours($slaHours);
            }

            $workOrder->update($updates);
            $workOrder->refresh();

            AuditLog::record(
                action: 'work_order_assigned',
                model: $workOrder,
                oldValues: ['assigned_to' => $previousAssignee],
                newValues: ['assigned_to' => $user->id, 'assignee_name' => $user->name],
            );

            return $workOrder;
        });
    }

    // ────────────────────────────────────────────────────────────────
    // Query methods (preserved from original implementation)
    // ────────────────────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(int $tenantId, array $filters = []): LengthAwarePaginator
    {
        $query = WorkOrder::where('tenant_id', $tenantId)
            ->with(['project:id,name', 'assignee:id,name', 'asset:id,name,asset_tag']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', (int) $filters['assigned_to']);
        }

        if (isset($filters['project_id'])) {
            $query->where('project_id', (int) $filters['project_id']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['sla_breached'])) {
            $query->where('sla_breached', filter_var($filters['sla_breached'], FILTER_VALIDATE_BOOLEAN));
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $allowedSorts = ['created_at', 'updated_at', 'sla_deadline', 'priority', 'status'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        return $query->paginate(
            perPage: min((int) ($filters['per_page'] ?? 20), 100),
        );
    }

    public function find(int $tenantId, int $id): ?WorkOrder
    {
        return WorkOrder::where('tenant_id', $tenantId)
            ->with([
                'project:id,name',
                'asset:id,name,asset_tag,qr_code',
                'location:id,name,type',
                'issue:id,title,status,priority',
                'assignee:id,name,email',
                'creator:id,name,email',
            ])
            ->find($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $tenantId, int $createdBy, array $data): WorkOrder
    {
        $data['tenant_id'] = $tenantId;
        $data['created_by'] = $createdBy;
        $data['wo_number'] = WorkOrder::generateWoNumber();
        $data['status'] = $data['status'] ?? 'pending';

        $priority = $data['priority'] ?? 'medium';
        $slaHours = (int) ($data['sla_hours'] ?? (Priority::tryFrom($priority)?->slaHours() ?? Priority::Medium->slaHours()));
        $data['sla_hours'] = $slaHours;

        if (! isset($data['sla_deadline'])) {
            $data['sla_deadline'] = Carbon::now()->addHours($slaHours);
        }

        $workOrder = WorkOrder::create($data);

        AuditLog::record(
            action: 'work_order_created',
            model: $workOrder,
            newValues: [
                'source'     => 'manual',
                'created_by' => $createdBy,
                'priority'   => $priority,
                'sla_hours'  => $slaHours,
            ],
        );

        return $workOrder;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $tenantId, int $id, array $data): ?WorkOrder
    {
        $workOrder = WorkOrder::where('tenant_id', $tenantId)->find($id);

        if (! $workOrder) {
            return null;
        }

        $oldValues = $workOrder->only(array_keys($data));

        unset($data['tenant_id'], $data['wo_number'], $data['created_by']);

        $workOrder->update($data);
        $workOrder->refresh();

        AuditLog::record(
            action: 'work_order_updated',
            model: $workOrder,
            oldValues: $oldValues,
            newValues: $data,
        );

        return $workOrder;
    }

    /**
     * Transition status using tenant-scoped lookup (legacy API convenience method).
     */
    public function transitionStatus(int $tenantId, int $id, string $newStatus, ?string $notes = null): ?WorkOrder
    {
        $workOrder = WorkOrder::where('tenant_id', $tenantId)->find($id);

        if (! $workOrder) {
            return null;
        }

        try {
            return $this->updateStatus($workOrder, $newStatus, $notes);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

}
