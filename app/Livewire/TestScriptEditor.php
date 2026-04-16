<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\TestScript;
use App\Models\TestStep;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Edit a tenant-owned FPT script — metadata, steps, and publication state.
 *
 * System scripts are read-only here; tenants wanting to customise one
 * should clone it (not yet implemented; noted as a roadmap item).
 */
class TestScriptEditor extends Component
{
    public TestScript $script;

    public string $name = '';

    public string $description = '';

    public string $systemType = '';

    public string $cxLevel = '';

    public ?int $estimatedMinutes = null;

    public string $newTitle = '';

    public string $newInstruction = '';

    public string $newMeasurementType = 'none';

    public string $newExpectedValue = '';

    public ?string $newExpectedNumeric = null;

    public ?string $newTolerance = null;

    public string $newUnit = '';

    public string $newSensorKey = '';

    public bool $newIsCritical = false;

    public bool $newRequiresPhoto = false;

    public bool $newRequiresWitness = false;

    public bool $newAutoEvaluate = false;

    public string $newEvaluationMode = 'within_tolerance';

    public ?string $newAcceptableMin = null;

    public ?string $newAcceptableMax = null;

    public function mount(int $scriptId): void
    {
        $tenantId = auth()->user()->tenant_id;

        $script = TestScript::availableTo($tenantId)->findOrFail($scriptId);

        abort_if($script->is_system, 403, 'System scripts are read-only.');

        $this->script = $script;
        $this->name = $script->name;
        $this->description = (string) $script->description;
        $this->systemType = $script->system_type;
        $this->cxLevel = (string) ($script->cx_level ?? '');
        $this->estimatedMinutes = $script->estimated_duration_minutes;
    }

    public function getStepsProperty()
    {
        return $this->script->steps()->get();
    }

    public function saveMetadata(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'systemType' => ['required', 'string', 'max:60'],
            'cxLevel' => ['nullable', 'in:L1,L2,L3,L4,L5'],
            'description' => ['nullable', 'string'],
            'estimatedMinutes' => ['nullable', 'integer', 'min:1', 'max:480'],
        ]);

        $this->script->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'system_type' => $this->systemType,
            'cx_level' => $this->cxLevel ?: null,
            'estimated_duration_minutes' => $this->estimatedMinutes,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Script updated.');
    }

    public function addStep(): void
    {
        $this->validate([
            'newTitle' => ['required', 'string', 'min:3', 'max:200'],
            'newInstruction' => ['required', 'string', 'min:5'],
            'newMeasurementType' => ['required', 'in:numeric,boolean,selection,text,none'],
            'newExpectedValue' => ['nullable', 'string', 'max:120'],
            'newExpectedNumeric' => ['nullable', 'numeric'],
            'newTolerance' => ['nullable', 'numeric'],
            'newUnit' => ['nullable', 'string', 'max:40'],
            'newSensorKey' => ['nullable', 'string', 'max:80'],
            'newEvaluationMode' => ['required', 'in:within_tolerance,greater_than_or_equal,less_than_or_equal,between,exact'],
            'newAcceptableMin' => ['nullable', 'numeric'],
            'newAcceptableMax' => ['nullable', 'numeric'],
        ]);

        DB::transaction(function (): void {
            $next = (int) ($this->script->steps()->max('sequence') ?? 0) + 1;

            TestStep::create([
                'test_script_id' => $this->script->id,
                'sequence' => $next,
                'title' => $this->newTitle,
                'instruction' => $this->newInstruction,
                'measurement_type' => $this->newMeasurementType,
                'expected_value' => $this->newExpectedValue ?: null,
                'expected_numeric' => is_numeric($this->newExpectedNumeric)
                    ? (float) $this->newExpectedNumeric
                    : null,
                'tolerance' => is_numeric($this->newTolerance)
                    ? (float) $this->newTolerance
                    : null,
                'measurement_unit' => $this->newUnit ?: null,
                'sensor_metric_key' => $this->newSensorKey ?: null,
                'is_critical' => $this->newIsCritical,
                'requires_photo' => $this->newRequiresPhoto,
                'requires_witness' => $this->newRequiresWitness,
                'auto_evaluate' => $this->newAutoEvaluate && $this->newMeasurementType === 'numeric',
                'evaluation_mode' => $this->newEvaluationMode,
                'acceptable_min' => is_numeric($this->newAcceptableMin) ? (float) $this->newAcceptableMin : null,
                'acceptable_max' => is_numeric($this->newAcceptableMax) ? (float) $this->newAcceptableMax : null,
            ]);
        });

        $this->reset([
            'newTitle', 'newInstruction', 'newExpectedValue', 'newExpectedNumeric',
            'newTolerance', 'newUnit', 'newSensorKey',
            'newIsCritical', 'newRequiresPhoto', 'newRequiresWitness',
            'newAutoEvaluate', 'newAcceptableMin', 'newAcceptableMax',
        ]);
        $this->newMeasurementType = 'none';
        $this->newEvaluationMode = 'within_tolerance';

        $this->dispatch('toast', type: 'success', message: 'Step added.');
    }

    public function deleteStep(int $stepId): void
    {
        $step = $this->script->steps()->where('id', $stepId)->firstOrFail();
        $deletedSeq = $step->sequence;
        $step->delete();

        $this->script->steps()
            ->where('sequence', '>', $deletedSeq)
            ->get()
            ->each(fn ($s) => $s->update(['sequence' => $s->sequence - 1]));

        $this->dispatch('toast', type: 'success', message: 'Step removed.');
    }

    public function moveStep(int $stepId, string $direction): void
    {
        $step = $this->script->steps()->where('id', $stepId)->firstOrFail();

        $swap = $this->script->steps()
            ->where('sequence', $direction === 'up' ? $step->sequence - 1 : $step->sequence + 1)
            ->first();

        if ($swap === null) {
            return;
        }

        DB::transaction(function () use ($step, $swap): void {
            $a = $step->sequence;
            $b = $swap->sequence;

            $step->update(['sequence' => 999999]);
            $swap->update(['sequence' => $a]);
            $step->update(['sequence' => $b]);
        });
    }

    public function publish(): void
    {
        if ($this->script->steps()->count() === 0) {
            $this->dispatch('toast', type: 'error', message: 'Cannot publish a script with no steps.');

            return;
        }

        $this->script->update([
            'status' => TestScript::STATUS_PUBLISHED,
            'version' => $this->script->version + ($this->script->status === TestScript::STATUS_PUBLISHED ? 1 : 0),
        ]);

        $this->dispatch('toast', type: 'success', message: "Script published as v{$this->script->version}.");
    }

    public function unpublish(): void
    {
        $this->script->update(['status' => TestScript::STATUS_DRAFT]);
        $this->dispatch('toast', type: 'success', message: 'Script reverted to draft.');
    }

    public function render()
    {
        return view('livewire.test-script-editor');
    }
}
