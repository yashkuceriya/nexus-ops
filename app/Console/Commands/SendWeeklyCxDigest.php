<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ChecklistCompletion;
use App\Models\ChecklistTemplate;
use App\Models\Issue;
use App\Models\Tenant;
use App\Models\TestExecution;
use App\Models\User;
use App\Notifications\WeeklyCxDigestNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Computes a per-tenant commissioning snapshot for the prior 7 days and
 * dispatches the `WeeklyCxDigestNotification` to each admin user.
 *
 * Designed to be scheduled weekly (Monday 07:00) via the console
 * scheduler, but runnable ad-hoc during demos with `--tenant=…` to
 * preview without touching the rest of the estate.
 */
class SendWeeklyCxDigest extends Command
{
    protected $signature = 'cx:weekly-digest
                            {--tenant= : Limit to a specific tenant id}
                            {--dry : Print the snapshot without sending notifications}';

    protected $description = 'Email admins a weekly commissioning programme snapshot (FPT, PFC, deficiencies).';

    public function handle(): int
    {
        $tenants = Tenant::query()
            ->when($this->option('tenant'), fn ($q) => $q->where('id', (int) $this->option('tenant')))
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants matched.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $snapshot = $this->buildSnapshot($tenant->id);

            $this->line(sprintf(
                '[%s] FPT %d · pass %.1f%% · PFC %d · clean %.1f%% · open %d',
                $tenant->name,
                $snapshot['fpt_total'],
                (float) $snapshot['fpt_pass_rate'],
                $snapshot['pfc_total'],
                (float) $snapshot['pfc_clean_rate'],
                $snapshot['open_deficiencies'],
            ));

            if ($this->option('dry')) {
                continue;
            }

            $admins = User::query()
                ->where('tenant_id', $tenant->id)
                ->whereIn('role', ['admin', 'owner', 'cx_manager'])
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new WeeklyCxDigestNotification($snapshot));
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSnapshot(int $tenantId): array
    {
        $weekAgo = Carbon::now()->subDays(7);
        $now = Carbon::now();

        // FPT over the last 7 days.
        $fpt = TestExecution::query()
            ->where('tenant_id', $tenantId)
            ->where('started_at', '>=', $weekAgo)
            ->get(['status', 'witness_signed_at']);

        $fptTotal = $fpt->count();
        $fptPassed = $fpt->where('status', TestExecution::STATUS_PASSED)->count();
        $fptFailed = $fpt->where('status', TestExecution::STATUS_FAILED)->count();
        $fptWitnessed = $fpt->whereNotNull('witness_signed_at')->count();
        $fptComplete = $fptPassed + $fptFailed;

        // PFC lifetime snapshot (PFCs are long-lived, weekly window is too short).
        $pfc = ChecklistCompletion::query()
            ->where('tenant_id', $tenantId)
            ->where('type', ChecklistTemplate::TYPE_PFC)
            ->get(['status', 'pass_count', 'fail_count', 'na_count']);

        $pfcTotal = $pfc->count();
        $pfcCompleted = $pfc->where('status', ChecklistCompletion::STATUS_COMPLETED)->count();
        $pfcFailed = $pfc->where('status', ChecklistCompletion::STATUS_FAILED)->count();
        $pfcInFlight = $pfc->where('status', ChecklistCompletion::STATUS_IN_PROGRESS)->count();
        $pfcFinal = $pfcCompleted + $pfcFailed;

        // Deficiency velocity.
        $openDefs = Issue::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $defsOpened = Issue::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', $weekAgo)
            ->count();

        $defsClosed = Issue::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'closed')
            ->where('resolved_at', '>=', $weekAgo)
            ->count();

        // Top failing script in the window.
        $topFailing = null;
        $grouped = $fpt
            ->whereIn('status', [TestExecution::STATUS_PASSED, TestExecution::STATUS_FAILED]);

        if ($grouped->count() > 0) {
            $byScript = TestExecution::query()
                ->where('tenant_id', $tenantId)
                ->where('started_at', '>=', $weekAgo)
                ->whereIn('status', [TestExecution::STATUS_PASSED, TestExecution::STATUS_FAILED])
                ->get(['test_script_id', 'test_script_name', 'status'])
                ->groupBy('test_script_id')
                ->map(function ($rows) {
                    $total = $rows->count();
                    $failed = $rows->where('status', TestExecution::STATUS_FAILED)->count();

                    return [
                        'name' => $rows->first()->test_script_name,
                        'runs' => $total,
                        'failed' => $failed,
                        'fail_rate' => $total > 0 ? round(($failed / $total) * 100, 1) : 0.0,
                    ];
                })
                ->filter(fn ($r) => $r['failed'] > 0)
                ->sortByDesc('fail_rate')
                ->first();

            $topFailing = $byScript;
        }

        return [
            'period_label' => sprintf('%s – %s', $weekAgo->format('M d'), $now->format('M d, Y')),
            'fpt_total' => $fptTotal,
            'fpt_passed' => $fptPassed,
            'fpt_failed' => $fptFailed,
            'fpt_witnessed' => $fptWitnessed,
            'fpt_pass_rate' => $fptComplete > 0 ? round(($fptPassed / $fptComplete) * 100, 1) : 0.0,
            'pfc_total' => $pfcTotal,
            'pfc_completed' => $pfcCompleted,
            'pfc_failed' => $pfcFailed,
            'pfc_in_flight' => $pfcInFlight,
            'pfc_clean_rate' => $pfcFinal > 0 ? round(($pfcCompleted / $pfcFinal) * 100, 1) : 0.0,
            'open_deficiencies' => $openDefs,
            'deficiencies_opened_this_week' => $defsOpened,
            'deficiencies_closed_this_week' => $defsClosed,
            'top_failing_script' => $topFailing,
        ];
    }
}
