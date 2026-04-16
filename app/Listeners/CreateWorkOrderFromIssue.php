<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\IssueImported;
use App\Models\Issue;
use App\Services\AutomationEngine;
use App\Services\WorkOrder\WorkOrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CreateWorkOrderFromIssue implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        private readonly WorkOrderService $workOrderService,
        private readonly AutomationEngine $automationEngine,
    ) {}

    public function handle(IssueImported $event): void
    {
        // Only auto-create work orders for newly imported issues
        if (! $event->wasCreated) {
            return;
        }

        $issue = Issue::find($event->issueId);

        if (! $issue) {
            Log::warning('Issue not found for work order creation.', [
                'issue_id' => $event->issueId,
            ]);

            return;
        }

        // Skip if a work order already exists for this issue
        if ($issue->workOrder()->exists()) {
            Log::info('Work order already exists for issue, skipping.', [
                'issue_id' => $issue->id,
            ]);

            return;
        }

        // Only create work orders for open/actionable issues
        if (! $issue->isOpen()) {
            Log::info('Issue is not open, skipping work order creation.', [
                'issue_id' => $issue->id,
                'status'   => $issue->status,
            ]);

            return;
        }

        $workOrder = $this->workOrderService->createFromIssue($issue);

        Log::info('Work order created from imported issue.', [
            'issue_id'      => $issue->id,
            'work_order_id' => $workOrder->id,
            'wo_number'     => $workOrder->wo_number,
        ]);

        // Evaluate automation rules for the issue_imported trigger
        $this->automationEngine->evaluateRules('issue_imported', [
            'work_order_id' => $workOrder->id,
            'issue_id' => $issue->id,
            'priority' => $issue->priority,
            'status' => $issue->status,
            'system_type' => $issue->asset?->system_type,
            'project_id' => $issue->project_id,
            'assigned_to' => $workOrder->assigned_to,
        ], $issue->tenant_id);
    }

    public function failed(IssueImported $event, \Throwable $exception): void
    {
        Log::error('Failed to create work order from imported issue.', [
            'issue_id'  => $event->issueId,
            'error'     => $exception->getMessage(),
            'trace'     => $exception->getTraceAsString(),
        ]);
    }
}
