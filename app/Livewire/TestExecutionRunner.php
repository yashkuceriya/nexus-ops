<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\SensorSource;
use App\Models\TestExecution;
use App\Models\TestStepResult;
use App\Services\TestExecution\TestExecutionService;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The step-by-step wizard for running a Functional Performance Test.
 *
 * Operates against one in-flight `TestExecution` and walks through its
 * captured step snapshots. For numeric steps the runner will opportunistically
 * pre-fill the measurement from the most recent BMS sensor reading tagged
 * with a matching metric key, giving the tech one-tap confirmation instead of
 * manual data entry.
 */
class TestExecutionRunner extends Component
{
    public TestExecution $execution;

    public ?int $currentResultId = null;

    public string $measuredValue = '';

    public ?string $measuredNumeric = null;

    public string $notes = '';

    public string $completionNotes = '';

    public bool $showComplete = false;

    public bool $showWitness = false;

    public string $witnessSignatureImage = '';

    public function mount(int $executionId): void
    {
        $this->execution = TestExecution::where('tenant_id', auth()->user()->tenant_id)
            ->with(['asset:id,name,asset_tag,project_id', 'project:id,name', 'script:id,name,version'])
            ->findOrFail($executionId);

        $this->selectFirstPendingStep();
    }

    #[Computed]
    public function results()
    {
        return $this->execution->results()->get();
    }

    #[Computed]
    public function currentResult(): ?TestStepResult
    {
        if ($this->currentResultId === null) {
            return null;
        }

        return $this->execution->results()
            ->with(['issue:id,title,status,priority,asset_id'])
            ->where('id', $this->currentResultId)
            ->first();
    }

    #[Computed]
    public function bmsPrefill(): ?array
    {
        $result = $this->currentResult;
        if ($result === null || $result->measurement_type !== 'numeric') {
            return null;
        }

        $step = $result->step;
        if ($step === null || ! $step->sensor_metric_key) {
            return null;
        }

        $sensor = SensorSource::where('asset_id', $this->execution->asset_id)
            ->where('is_active', true)
            ->where(function ($q) use ($step): void {
                $q->where('sensor_type', $step->sensor_metric_key)
                    ->orWhere('external_id', $step->sensor_metric_key);
            })
            ->first();

        if ($sensor === null) {
            return null;
        }

        $reading = $sensor->readings()->latest('recorded_at')->first();
        if ($reading === null) {
            return null;
        }

        return [
            'value' => $reading->value,
            'unit' => $sensor->unit,
            'recorded_at' => $reading->recorded_at,
            'sensor_name' => $sensor->name,
        ];
    }

    public function selectStep(int $resultId): void
    {
        $this->currentResultId = $resultId;
        $current = $this->currentResult;
        if ($current) {
            $this->measuredValue = (string) ($current->measured_value ?? '');
            $this->measuredNumeric = $current->measured_numeric !== null
                ? (string) $current->measured_numeric
                : null;
            $this->notes = (string) ($current->notes ?? '');
        }
    }

    public function applyBmsPrefill(): void
    {
        $prefill = $this->bmsPrefill;
        if ($prefill === null) {
            return;
        }
        $this->measuredValue = (string) $prefill['value'];
        $this->measuredNumeric = (string) $prefill['value'];
        $this->notes = trim(($this->notes ? $this->notes."\n" : '')
            ."Auto-filled from BMS sensor '{$prefill['sensor_name']}' at "
            .$prefill['recorded_at']?->toIso8601String());
    }

    public function pass(TestExecutionService $service): void
    {
        $this->applyResult($service, TestStepResult::STATUS_PASS);
    }

    public function fail(TestExecutionService $service): void
    {
        $this->applyResult($service, TestStepResult::STATUS_FAIL);
    }

    public function skip(TestExecutionService $service): void
    {
        $this->applyResult($service, TestStepResult::STATUS_SKIPPED);
    }

    public function markNa(TestExecutionService $service): void
    {
        $this->applyResult($service, TestStepResult::STATUS_NA);
    }

    public function complete(TestExecutionService $service): void
    {
        try {
            $service->complete($this->execution, auth()->user(), $this->completionNotes ?: null);
            $this->execution->refresh();
            $this->showComplete = false;
            $this->completionNotes = '';
            $this->dispatch('toast', type: 'success', message: "Execution closed as {$this->execution->status}.");
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function witness(TestExecutionService $service): void
    {
        try {
            $service->witnessSign(
                execution: $this->execution,
                witness: auth()->user(),
                signatureImage: $this->witnessSignatureImage !== '' ? $this->witnessSignatureImage : null,
                request: request(),
            );
            $this->execution->refresh();
            $this->showWitness = false;
            $this->witnessSignatureImage = '';
            $this->dispatch('toast', type: 'success', message: 'Execution witnessed and signed.');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    /**
     * Live preview of what auto-evaluation would decide for the current
     * numeric measurement. Returned as pass/fail so the UI can show a hint
     * before the operator commits the step.
     */
    #[Computed]
    public function autoEvalPreview(): ?string
    {
        $result = $this->currentResult;
        if ($result === null || $result->measurement_type !== 'numeric') {
            return null;
        }

        $step = $result->step;
        if ($step === null || ! $step->auto_evaluate) {
            return null;
        }

        if (! is_numeric($this->measuredNumeric)) {
            return null;
        }

        return $step->evaluateNumeric((float) $this->measuredNumeric);
    }

    public function retest(TestExecutionService $service): void
    {
        try {
            $new = $service->retest($this->execution, auth()->user());
            $this->redirectRoute('fpt.run', ['executionId' => $new->id], navigate: true);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    private function applyResult(TestExecutionService $service, string $status): void
    {
        $result = $this->currentResult;
        if ($result === null) {
            return;
        }

        $numeric = null;
        if ($result->measurement_type === 'numeric' && is_numeric($this->measuredNumeric)) {
            $numeric = (float) $this->measuredNumeric;
        }

        try {
            $service->recordStepResult(
                result: $result,
                recordedBy: auth()->user(),
                status: $status,
                measuredValue: $this->measuredValue !== '' ? $this->measuredValue : null,
                measuredNumeric: $numeric,
                notes: $this->notes !== '' ? $this->notes : null,
            );

            $this->execution->refresh();
            $this->measuredValue = '';
            $this->measuredNumeric = null;
            $this->notes = '';
            $this->selectFirstPendingStep();
            $this->dispatch('toast', type: 'success', message: "Step marked {$status}.");
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    private function selectFirstPendingStep(): void
    {
        $next = $this->execution->results()
            ->where('status', TestStepResult::STATUS_PENDING)
            ->orderBy('step_sequence')
            ->first();

        $this->currentResultId = $next?->id;
        $this->measuredValue = '';
        $this->measuredNumeric = null;
        $this->notes = '';
    }

    public function render()
    {
        return view('livewire.test-execution-runner');
    }
}
