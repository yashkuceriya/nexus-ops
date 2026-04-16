<?php

namespace App\Console\Commands;

use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use App\Services\WorkOrder\WorkOrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GeneratePreventiveWorkOrders extends Command
{
    protected $signature = 'pm:generate';

    protected $description = 'Generate preventive maintenance work orders for schedules that are due';

    public function handle(WorkOrderService $workOrderService): int
    {
        $this->info('Scanning for due preventive maintenance schedules...');

        $dueSchedules = MaintenanceSchedule::where('is_active', true)
            ->whereNotNull('next_due_date')
            ->where('next_due_date', '<=', now()->toDateString())
            ->get();

        $generated = 0;
        $skipped = 0;

        foreach ($dueSchedules as $schedule) {
            // Idempotency check: skip if a preventive WO already exists for this schedule + due date
            $alreadyExists = WorkOrder::where('type', 'preventive')
                ->where('source', 'schedule')
                ->where('title', "PM: {$schedule->name}")
                ->where('asset_id', $schedule->asset_id)
                ->where('tenant_id', $schedule->tenant_id)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($alreadyExists) {
                $skipped++;
                $this->line("  Skipped (already exists): {$schedule->name}");

                continue;
            }

            try {
                $workOrder = $workOrderService->createFromSchedule($schedule);
                $schedule->calculateNextDueDate();
                $generated++;

                $this->line("  Created: {$workOrder->wo_number} - {$schedule->name}");
            } catch (\Throwable $e) {
                $this->error("  Failed: {$schedule->name} - {$e->getMessage()}");
                Log::error('PM generation failed', [
                    'schedule_id' => $schedule->id,
                    'schedule_name' => $schedule->name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $summary = "PM generation complete: {$generated} created, {$skipped} skipped (duplicates).";
        $this->info($summary);
        Log::info($summary);

        return self::SUCCESS;
    }
}
