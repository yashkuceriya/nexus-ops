<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TestExecutionCompleted;
use App\Models\TestExecution;
use App\Models\User;
use App\Notifications\TestExecutionFailedNotification;
use Illuminate\Support\Facades\Notification;

/**
 * When an FPT completes in a FAILED state, loop in project managers and
 * admins so the deficiency enters their workflow immediately instead of
 * waiting on the commissioning agent to email a PDF.
 *
 * Runs synchronously because each tenant's Cx run is already batched. If
 * projects get busier we can push this onto a tenant-aware queue.
 */
class NotifyOnFailedTestExecution
{
    public function handle(TestExecutionCompleted $event): void
    {
        $execution = $event->execution;

        if ($execution->status !== TestExecution::STATUS_FAILED) {
            return;
        }

        $recipients = User::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $execution->tenant_id)
            ->whereIn('role', ['admin', 'manager'])
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new TestExecutionFailedNotification($execution));
    }
}
