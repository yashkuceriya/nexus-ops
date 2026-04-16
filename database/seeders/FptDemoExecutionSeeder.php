<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\TestExecution;
use App\Models\TestScript;
use App\Models\TestStepResult;
use App\Models\User;
use App\Services\TestExecution\TestExecutionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Produces realistic demo FPT executions so the Commissioning Test Matrix
 * and downstream readiness score aren't empty on a fresh install.
 *
 * For each tenant we pair the most-relevant system FPT script with a
 * matching asset and simulate:
 *   - one PASS execution (witnessed + signed with a tiny signature PNG)
 *   - one FAIL execution (which auto-creates a deficiency issue)
 *   - one IN-PROGRESS execution (partial results)
 *
 * The output is good enough to film a demo against without any seeding gap.
 */
class FptDemoExecutionSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(TestExecutionService::class);

        $tenants = User::query()
            ->withoutGlobalScope('tenant')
            ->whereNotNull('tenant_id')
            ->whereIn('role', ['admin', 'manager'])
            ->get()
            ->groupBy('tenant_id');

        foreach ($tenants as $tenantId => $users) {
            $starter = $users->first();
            $witness = $users->skip(1)->first() ?? $starter;

            // Impersonate the starter so AuditLog::record() can resolve the
            // authenticated user's tenant while seeding.
            Auth::login($starter);

            $assets = Asset::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->whereNotNull('project_id')
                ->get();

            if ($assets->isEmpty()) {
                continue;
            }

            $scripts = TestScript::availableTo((int) $tenantId)
                ->published()
                ->get()
                ->keyBy('system_type');

            $planned = [
                ['category' => 'chiller', 'script' => 'chiller', 'outcome' => 'passed_witnessed'],
                ['category' => 'ups', 'script' => 'ups', 'outcome' => 'failed'],
                ['category' => 'generator', 'script' => 'generator', 'outcome' => 'passed'],
                ['category' => 'crac', 'script' => 'crac', 'outcome' => 'in_progress'],
                ['category' => 'ats', 'script' => 'ats', 'outcome' => 'passed_witnessed'],
            ];

            foreach ($planned as $plan) {
                $asset = $assets->first(fn ($a) => strtolower((string) $a->category) === $plan['category']
                    || strtolower((string) $a->system_type) === $plan['category']);

                if ($asset === null || ! $scripts->has($plan['script'])) {
                    continue;
                }

                $script = $scripts[$plan['script']];

                $exists = TestExecution::withoutGlobalScope('tenant')
                    ->where('tenant_id', $tenantId)
                    ->where('asset_id', $asset->id)
                    ->where('test_script_id', $script->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                try {
                    $this->simulateExecution($service, $script, $asset, $starter, $witness, $plan['outcome']);
                } catch (\Throwable $e) {
                    $this->command?->error('FPT demo seed skipped: '.$e->getMessage());
                }
            }

            Auth::logout();
        }
    }

    private function simulateExecution(
        TestExecutionService $service,
        TestScript $script,
        Asset $asset,
        User $starter,
        User $witness,
        string $outcome,
    ): void {
        $execution = $service->start(
            script: $script,
            asset: $asset,
            startedBy: $starter,
            witnessId: $witness->id,
        );

        // Back-date the start so charts/trends look plausible.
        $execution->update(['started_at' => Carbon::now()->subDays(random_int(2, 21))]);

        $results = $execution->results()->orderBy('step_sequence')->get();

        foreach ($results as $index => $result) {
            if ($outcome === 'in_progress' && $index >= ceil($results->count() / 2)) {
                break;
            }

            $forceFail = $outcome === 'failed' && $index === 1;
            $status = $forceFail ? TestStepResult::STATUS_PASS : TestStepResult::STATUS_PASS;

            // Provide a measurement tailored to the step's type so the row
            // renders richly in the UI.
            [$measuredValue, $measuredNumeric] = $this->fabricateMeasurement($result, $forceFail);

            if ($result->measurement_type === 'numeric' && $forceFail) {
                $status = TestStepResult::STATUS_FAIL;
            } elseif ($result->measurement_type === 'boolean' && $forceFail) {
                $status = TestStepResult::STATUS_FAIL;
            }

            $service->recordStepResult(
                result: $result,
                recordedBy: $starter,
                status: $status,
                measuredValue: $measuredValue,
                measuredNumeric: $measuredNumeric,
                notes: $forceFail ? 'Out of spec — deficiency documented for retest.' : null,
            );
        }

        if ($outcome === 'in_progress') {
            return;
        }

        $service->complete($execution, $starter, 'Seed demo execution.');

        if (in_array($outcome, ['passed_witnessed'], true)) {
            // 1×1 transparent PNG data URL so the PDF always has something.
            $stubSig = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
            $service->witnessSign($execution->fresh(), $witness, $stubSig);
        }
    }

    /**
     * Fabricate a measurement that will deterministically pass (or fail)
     * the step's auto-evaluation rules so seeded demo data is stable.
     *
     * @return array{0: ?string, 1: ?float}
     */
    private function fabricateMeasurement(TestStepResult $result, bool $forceFail): array
    {
        if ($result->measurement_type === 'numeric') {
            $value = $this->safeNumericValue($result, $forceFail);

            if ($value === null) {
                return [null, null];
            }

            return [number_format($value, 2, '.', ''), $value];
        }

        if ($result->measurement_type === 'boolean') {
            return [$forceFail ? 'no' : 'yes', null];
        }

        if ($result->measurement_type === 'text') {
            return ['No deviations observed from design intent.', null];
        }

        return [null, null];
    }

    private function safeNumericValue(TestStepResult $result, bool $forceFail): ?float
    {
        $expected = $result->expected_numeric !== null ? (float) $result->expected_numeric : null;
        $tolerance = $result->tolerance !== null ? (float) $result->tolerance : null;
        $min = $result->acceptable_min !== null ? (float) $result->acceptable_min : null;
        $max = $result->acceptable_max !== null ? (float) $result->acceptable_max : null;
        $mode = $result->evaluation_mode ?: 'within_tolerance';

        return match ($mode) {
            'within_tolerance' => $this->valueInTolerance($expected, $tolerance, $forceFail),
            'greater_than_or_equal' => $this->valueGte($expected ?? $min, $forceFail),
            'less_than_or_equal' => $this->valueLte($expected ?? $max, $forceFail),
            'between' => $this->valueBetween($min, $max, $forceFail),
            'exact' => $expected,
            default => $expected,
        };
    }

    private function valueInTolerance(?float $expected, ?float $tolerance, bool $forceFail): ?float
    {
        if ($expected === null) {
            return null;
        }

        $tol = $tolerance !== null && $tolerance > 0 ? $tolerance : max(0.1, abs($expected) * 0.05);

        return $forceFail
            ? $expected + $tol * 3
            : $expected + (mt_rand(-30, 30) / 100) * $tol;
    }

    private function valueGte(?float $threshold, bool $forceFail): ?float
    {
        if ($threshold === null) {
            return null;
        }

        $bump = max(0.5, abs($threshold) * 0.1);

        return $forceFail ? $threshold - $bump : $threshold + $bump;
    }

    private function valueLte(?float $threshold, bool $forceFail): ?float
    {
        if ($threshold === null) {
            return null;
        }

        $bump = max(0.5, abs($threshold) * 0.1);

        return $forceFail ? $threshold + $bump : max(0.0, $threshold - $bump);
    }

    private function valueBetween(?float $min, ?float $max, bool $forceFail): ?float
    {
        if ($min === null && $max === null) {
            return null;
        }

        $lo = $min ?? ($max !== null ? $max - 1 : 0);
        $hi = $max ?? ($min + 1);

        if ($forceFail) {
            return $hi + max(0.5, abs($hi - $lo));
        }

        return $lo + ($hi - $lo) / 2;
    }
}
