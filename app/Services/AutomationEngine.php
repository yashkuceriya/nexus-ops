<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\AutomationRule;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

final class AutomationEngine
{
    /**
     * Main entry point: evaluate all active rules for a given trigger type and tenant.
     */
    public function evaluateRules(string $triggerType, array $context, int $tenantId): void
    {
        $rules = AutomationRule::where('tenant_id', $tenantId)
            ->where('trigger_type', $triggerType)
            ->where('is_active', true)
            ->get();

        foreach ($rules as $rule) {
            try {
                if ($this->checkConditions($rule->conditions ?? [], $context)) {
                    $this->executeActions($rule->actions ?? [], $context, $tenantId);

                    $rule->increment('execution_count');
                    $rule->update(['last_executed_at' => now()]);

                    AuditLog::record(
                        action: 'automation_rule_executed',
                        model: $rule,
                        newValues: [
                            'trigger_type' => $triggerType,
                            'context' => array_intersect_key($context, array_flip([
                                'work_order_id', 'issue_id', 'priority', 'status', 'system_type', 'sensor_type',
                            ])),
                            'actions_count' => count($rule->actions ?? []),
                        ],
                        source: 'automation',
                    );

                    Log::info('Automation rule executed.', [
                        'rule_id' => $rule->id,
                        'rule_name' => $rule->name,
                        'trigger_type' => $triggerType,
                        'tenant_id' => $tenantId,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Automation rule execution failed.', [
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Evaluate all conditions using AND logic.
     */
    public function checkConditions(array $conditions, array $context): bool
    {
        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? 'equals';
            $expected = $condition['value'] ?? null;
            $actual = $context[$field] ?? null;

            if ($field === null) {
                continue;
            }

            if (! $this->evaluateCondition($actual, $operator, $expected)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Execute all actions in a rule.
     */
    public function executeActions(array $actions, array $context, int $tenantId): void
    {
        foreach ($actions as $action) {
            $type = $action['type'] ?? null;

            if ($type === null) {
                continue;
            }

            try {
                match ($type) {
                    'assign_to_user' => $this->actionAssignToUser($action, $context, $tenantId),
                    'change_priority' => $this->actionChangePriority($action, $context),
                    'send_notification' => $this->actionSendNotification($action, $context, $tenantId),
                    'create_work_order' => $this->actionCreateWorkOrder($action, $context, $tenantId),
                    'escalate_to_manager' => $this->actionEscalateToManager($action, $context, $tenantId),
                    default => Log::warning("Unknown automation action type: {$type}"),
                };
            } catch (\Throwable $e) {
                Log::error("Automation action '{$type}' failed.", [
                    'action' => $action,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    // ────────────────────────────────────────────────────────────────
    // Condition evaluation
    // ────────────────────────────────────────────────────────────────

    private function evaluateCondition(mixed $actual, string $operator, mixed $expected): bool
    {
        return match ($operator) {
            'equals' => (string) $actual === (string) $expected,
            'not_equals' => (string) $actual !== (string) $expected,
            'greater_than' => is_numeric($actual) && is_numeric($expected) && (float) $actual > (float) $expected,
            'less_than' => is_numeric($actual) && is_numeric($expected) && (float) $actual < (float) $expected,
            'contains' => is_string($actual) && str_contains(strtolower($actual), strtolower((string) $expected)),
            'in' => is_array($expected) ? in_array((string) $actual, array_map('strval', $expected), true) : in_array((string) $actual, array_map('trim', explode(',', (string) $expected)), true),
            default => false,
        };
    }

    // ────────────────────────────────────────────────────────────────
    // Action handlers
    // ────────────────────────────────────────────────────────────────

    private function actionAssignToUser(array $action, array $context, int $tenantId): void
    {
        $userId = $action['user_id'] ?? null;
        $workOrderId = $context['work_order_id'] ?? null;

        if (! $userId || ! $workOrderId) {
            return;
        }

        $workOrder = WorkOrder::where('tenant_id', $tenantId)->find($workOrderId);
        $user = User::where('tenant_id', $tenantId)->find($userId);

        if ($workOrder && $user) {
            $previousAssignee = $workOrder->assigned_to;
            $workOrder->update([
                'assigned_to' => $user->id,
                'status' => $workOrder->status === 'open' || $workOrder->status === 'pending' ? 'assigned' : $workOrder->status,
            ]);

            AuditLog::record(
                action: 'automation_assigned_user',
                model: $workOrder,
                oldValues: ['assigned_to' => $previousAssignee],
                newValues: ['assigned_to' => $user->id, 'assignee_name' => $user->name],
                source: 'automation',
            );
        }
    }

    private function actionChangePriority(array $action, array $context): void
    {
        $priority = $action['priority'] ?? null;
        $workOrderId = $context['work_order_id'] ?? null;

        if (! $priority || ! $workOrderId) {
            return;
        }

        $workOrder = WorkOrder::find($workOrderId);

        if ($workOrder) {
            $oldPriority = $workOrder->priority;
            $workOrder->update(['priority' => $priority]);

            AuditLog::record(
                action: 'automation_changed_priority',
                model: $workOrder,
                oldValues: ['priority' => $oldPriority],
                newValues: ['priority' => $priority],
                source: 'automation',
            );
        }
    }

    private function actionSendNotification(array $action, array $context, int $tenantId): void
    {
        $channel = $action['channel'] ?? 'database';
        $workOrderId = $context['work_order_id'] ?? null;

        $workOrder = $workOrderId ? WorkOrder::find($workOrderId) : null;
        $message = $action['message'] ?? 'Automation rule triggered for work order ' . ($workOrder->wo_number ?? '#' . $workOrderId);

        // Determine recipients
        $recipientId = $action['user_id'] ?? $context['assigned_to'] ?? null;

        if ($recipientId) {
            $user = User::where('tenant_id', $tenantId)->find($recipientId);

            if ($user) {
                $user->notify(new \Illuminate\Notifications\Messages\DatabaseMessage([
                    'title' => 'Automation Alert',
                    'message' => $message,
                    'work_order_id' => $workOrderId,
                    'channel' => $channel,
                ]));
            }
        }
    }

    private function actionCreateWorkOrder(array $action, array $context, int $tenantId): void
    {
        $template = $action['template'] ?? [];

        if (empty($template)) {
            return;
        }

        $workOrder = WorkOrder::create([
            'tenant_id' => $tenantId,
            'project_id' => $template['project_id'] ?? $context['project_id'] ?? null,
            'asset_id' => $template['asset_id'] ?? $context['asset_id'] ?? null,
            'wo_number' => WorkOrder::generateWoNumber(),
            'title' => $template['title'] ?? 'Auto-generated Work Order',
            'description' => $template['description'] ?? 'Created by automation rule.',
            'status' => 'open',
            'priority' => $template['priority'] ?? 'medium',
            'type' => $template['type'] ?? 'corrective',
            'source' => 'manual',
            'sla_hours' => $template['sla_hours'] ?? 24,
            'sla_deadline' => Carbon::now()->addHours($template['sla_hours'] ?? 24),
        ]);

        AuditLog::record(
            action: 'automation_created_work_order',
            model: $workOrder,
            newValues: [
                'source' => 'automation',
                'template' => $template,
            ],
            source: 'automation',
        );
    }

    private function actionEscalateToManager(array $action, array $context, int $tenantId): void
    {
        $managers = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['manager', 'admin', 'owner'])
            ->get();

        $workOrderId = $context['work_order_id'] ?? null;
        $workOrder = $workOrderId ? WorkOrder::find($workOrderId) : null;

        $message = $action['message'] ?? 'Escalation: Work order ' . ($workOrder->wo_number ?? '#' . $workOrderId) . ' requires manager attention.';

        foreach ($managers as $manager) {
            $manager->notify(new \Illuminate\Notifications\Messages\DatabaseMessage([
                'title' => 'Escalation Alert',
                'message' => $message,
                'work_order_id' => $workOrderId,
                'priority' => $context['priority'] ?? 'unknown',
            ]));
        }

        if ($workOrder) {
            AuditLog::record(
                action: 'automation_escalated_to_managers',
                model: $workOrder,
                newValues: [
                    'managers_notified' => $managers->pluck('name')->toArray(),
                    'manager_count' => $managers->count(),
                ],
                source: 'automation',
            );
        }
    }
}
