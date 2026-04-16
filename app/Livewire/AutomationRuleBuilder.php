<?php

namespace App\Livewire;

use App\Models\AutomationRule;
use App\Models\User;
use Livewire\Component;

class AutomationRuleBuilder extends Component
{
    public ?int $ruleId = null;

    public string $name = '';

    public string $description = '';

    public string $triggerType = 'work_order_created';

    public bool $isActive = true;

    /** @var array<int, array{field: string, operator: string, value: string}> */
    public array $conditions = [];

    /** @var array<int, array<string, mixed>> */
    public array $actions = [];

    public array $triggerTypes = [
        'work_order_created' => 'Work Order Created',
        'work_order_status_changed' => 'Work Order Status Changed',
        'sla_approaching' => 'SLA Approaching',
        'sla_breached' => 'SLA Breached',
        'sensor_alert' => 'Sensor Alert',
        'issue_imported' => 'Issue Imported',
        'pm_due' => 'Preventive Maintenance Due',
    ];

    public array $triggerDescriptions = [
        'work_order_created' => 'Fires when a new work order is created from any source.',
        'work_order_status_changed' => 'Fires when a work order status transitions to a new state.',
        'sla_approaching' => 'Fires when a work order is within 1 hour of its SLA deadline.',
        'sla_breached' => 'Fires when a work order exceeds its SLA deadline.',
        'sensor_alert' => 'Fires when an IoT sensor reading exceeds configured thresholds.',
        'issue_imported' => 'Fires when an issue is imported from an external system.',
        'pm_due' => 'Fires when a preventive maintenance schedule reaches its due date.',
    ];

    public array $conditionFields = [
        'priority' => 'Priority',
        'status' => 'Status',
        'system_type' => 'System Type',
        'project_id' => 'Project ID',
        'sensor_type' => 'Sensor Type',
        'type' => 'Work Order Type',
    ];

    public array $operators = [
        'equals' => 'Equals',
        'not_equals' => 'Not Equals',
        'greater_than' => 'Greater Than',
        'less_than' => 'Less Than',
        'contains' => 'Contains',
        'in' => 'In (comma-separated)',
    ];

    public array $actionTypes = [
        'assign_to_user' => 'Assign to User',
        'change_priority' => 'Change Priority',
        'send_notification' => 'Send Notification',
        'create_work_order' => 'Create Work Order',
        'escalate_to_manager' => 'Escalate to Manager',
    ];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $rule = AutomationRule::findOrFail($id);
            $this->ruleId = $rule->id;
            $this->name = $rule->name;
            $this->description = $rule->description ?? '';
            $this->triggerType = $rule->trigger_type;
            $this->isActive = $rule->is_active;
            $this->conditions = $rule->conditions ?? [];
            $this->actions = $rule->actions ?? [];
        }
    }

    public function addCondition(): void
    {
        $this->conditions[] = ['field' => 'priority', 'operator' => 'equals', 'value' => ''];
    }

    public function removeCondition(int $index): void
    {
        unset($this->conditions[$index]);
        $this->conditions = array_values($this->conditions);
    }

    public function addAction(): void
    {
        $this->actions[] = ['type' => 'assign_to_user', 'user_id' => '', 'priority' => '', 'channel' => 'email', 'message' => '', 'template' => []];
    }

    public function removeAction(int $index): void
    {
        unset($this->actions[$index]);
        $this->actions = array_values($this->actions);
    }

    public function getUsersProperty()
    {
        return User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'triggerType' => 'required|in:'.implode(',', array_keys($this->triggerTypes)),
            'conditions' => 'array',
            'conditions.*.field' => 'required|string',
            'conditions.*.operator' => 'required|string',
            'conditions.*.value' => 'required|string',
            'actions' => 'required|array|min:1',
            'actions.*.type' => 'required|in:'.implode(',', array_keys($this->actionTypes)),
        ]);

        // Clean action data — remove empty optional fields
        $cleanActions = collect($this->actions)->map(function ($action) {
            $clean = ['type' => $action['type']];
            match ($action['type']) {
                'assign_to_user' => $clean['user_id'] = (int) ($action['user_id'] ?? 0),
                'change_priority' => $clean['priority'] = $action['priority'] ?? 'high',
                'send_notification' => $clean = array_merge($clean, [
                    'channel' => $action['channel'] ?? 'email',
                    'message' => $action['message'] ?? '',
                ]),
                'create_work_order' => $clean['template'] = $action['template'] ?? [],
                'escalate_to_manager' => $clean['message'] = $action['message'] ?? '',
                default => null,
            };

            return $clean;
        })->toArray();

        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'trigger_type' => $this->triggerType,
            'is_active' => $this->isActive,
            'conditions' => $this->conditions,
            'actions' => $cleanActions,
        ];

        if ($this->ruleId) {
            AutomationRule::findOrFail($this->ruleId)
                ->update($data);
            session()->flash('success', 'Automation rule updated successfully.');
        } else {
            AutomationRule::create($data);
            session()->flash('success', 'Automation rule created successfully.');
        }

        $this->redirect(route('automation.index'), navigate: true);
    }

    public function render()
    {
        $title = $this->ruleId ? 'Edit Automation Rule' : 'Create Automation Rule';

        return view('livewire.automation-rule-builder')
            ->layout('layouts.app', ['title' => $title]);
    }
}
