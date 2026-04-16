<?php

namespace App\Livewire;

use App\Models\ChecklistCompletion;
use App\Models\ChecklistTemplate;
use Livewire\Component;

class ChecklistForm extends Component
{
    public int $workOrderId;

    public ?int $selectedTemplateId = null;

    public ?int $activeCompletionId = null;

    public array $steps = [];

    public array $responses = [];

    public int $currentStep = 0;

    public int $totalSteps = 0;

    public function mount(int $workOrderId): void
    {
        $this->workOrderId = $workOrderId;

        // Check for an existing in-progress completion
        $existing = ChecklistCompletion::where('work_order_id', $workOrderId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            $this->resumeCompletion($existing);
        }
    }

    public function getTemplatesProperty()
    {
        return ChecklistTemplate::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getCompletionsProperty()
    {
        return ChecklistCompletion::where('work_order_id', $this->workOrderId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with('template:id,name,category', 'completedByUser:id,name')
            ->orderByDesc('created_at')
            ->get();
    }

    public function startChecklist(int $templateId): void
    {
        $template = ChecklistTemplate::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->findOrFail($templateId);

        $completion = ChecklistCompletion::create([
            'tenant_id' => auth()->user()->tenant_id,
            'work_order_id' => $this->workOrderId,
            'checklist_template_id' => $template->id,
            'completed_by' => auth()->id(),
            'responses' => [],
            'status' => 'in_progress',
        ]);

        $this->resumeCompletion($completion);
    }

    private function resumeCompletion(ChecklistCompletion $completion): void
    {
        $this->activeCompletionId = $completion->id;
        $this->selectedTemplateId = $completion->checklist_template_id;

        $template = $completion->template;
        $this->steps = $template->steps ?? [];
        $this->totalSteps = count($this->steps);
        $this->responses = $completion->responses ?? [];

        // Resume at the first unanswered step
        $answeredOrders = collect($this->responses)->pluck('step_order')->all();
        $this->currentStep = 0;
        foreach ($this->steps as $i => $step) {
            if (! in_array($step['order'], $answeredOrders)) {
                $this->currentStep = $i;
                break;
            }
            if ($i === count($this->steps) - 1) {
                $this->currentStep = $i;
            }
        }
    }

    public function saveStepResponse(mixed $value): void
    {
        if ($this->activeCompletionId === null) {
            return;
        }

        $step = $this->steps[$this->currentStep] ?? null;
        if (! $step) {
            return;
        }

        $passed = null;
        if ($step['type'] === 'pass_fail') {
            $passed = $value === 'pass';
        } elseif ($step['type'] === 'numeric') {
            $numVal = (float) $value;
            $min = $step['min'] ?? null;
            $max = $step['max'] ?? null;
            $passed = true;
            if ($min !== null && $numVal < $min) {
                $passed = false;
            }
            if ($max !== null && $numVal > $max) {
                $passed = false;
            }
        }

        $response = [
            'step_order' => $step['order'],
            'value' => $value,
            'passed' => $passed,
        ];

        // Replace existing response for this step or add new one
        $responses = collect($this->responses)->reject(fn ($r) => $r['step_order'] === $step['order'])->values()->all();
        $responses[] = $response;
        $this->responses = $responses;

        // Persist to database
        $completion = ChecklistCompletion::find($this->activeCompletionId);
        if ($completion) {
            $completion->update(['responses' => $this->responses]);
        }

        // Advance to next step
        if ($this->currentStep < $this->totalSteps - 1) {
            $this->currentStep++;
        }
    }

    public function goToStep(int $index): void
    {
        if ($index >= 0 && $index < $this->totalSteps) {
            $this->currentStep = $index;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 0) {
            $this->currentStep--;
        }
    }

    public function completeChecklist(): void
    {
        $completion = ChecklistCompletion::find($this->activeCompletionId);
        if (! $completion) {
            return;
        }

        // Determine overall status: if any pass_fail step failed, status = failed
        $hasFail = collect($this->responses)->contains(function ($r) {
            return $r['passed'] === false;
        });

        $completion->update([
            'responses' => $this->responses,
            'status' => $hasFail ? 'failed' : 'completed',
            'completed_at' => now(),
        ]);

        // Reset form state
        $this->activeCompletionId = null;
        $this->selectedTemplateId = null;
        $this->steps = [];
        $this->responses = [];
        $this->currentStep = 0;
        $this->totalSteps = 0;

        session()->flash('checklist-success', $hasFail
            ? 'Checklist completed with failures. Review required.'
            : 'Checklist completed successfully.');
    }

    public function cancelChecklist(): void
    {
        if ($this->activeCompletionId) {
            ChecklistCompletion::where('id', $this->activeCompletionId)->delete();
        }

        $this->activeCompletionId = null;
        $this->selectedTemplateId = null;
        $this->steps = [];
        $this->responses = [];
        $this->currentStep = 0;
        $this->totalSteps = 0;
    }

    public function getResponseForCurrentStep(): ?array
    {
        $step = $this->steps[$this->currentStep] ?? null;
        if (! $step) {
            return null;
        }

        return collect($this->responses)->firstWhere('step_order', $step['order']);
    }

    public function render()
    {
        return view('livewire.checklist-form');
    }
}
