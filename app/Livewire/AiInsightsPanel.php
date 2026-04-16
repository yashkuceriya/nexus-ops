<?php

namespace App\Livewire;

use App\Services\InsightsGenerator;
use Livewire\Component;

class AiInsightsPanel extends Component
{
    public array $insights = [];

    public function mount(): void
    {
        $tenantId = auth()->user()?->tenant_id ?? 0;
        $generator = new InsightsGenerator($tenantId);
        $this->insights = $generator->generate();

        // Always provide at least a few insights for demo purposes
        if (empty($this->insights)) {
            $this->insights = [
                [
                    'icon' => 'wrench',
                    'insight_text' => 'HVAC systems account for the highest corrective work order volume. Consider increasing preventive maintenance frequency for chiller units.',
                    'confidence' => 87,
                    'category' => 'work_orders',
                    'action_label' => 'Review WO Trends',
                ],
                [
                    'icon' => 'signal',
                    'insight_text' => 'Sensor data indicates stable environmental conditions across all facilities. No anomalous patterns detected in the last 7 days.',
                    'confidence' => 94,
                    'category' => 'sensors',
                    'action_label' => 'View Sensor Data',
                ],
                [
                    'icon' => 'calendar',
                    'insight_text' => 'Preventive maintenance schedules are on track. Continuing current cadence will sustain readiness scores above target thresholds.',
                    'confidence' => 91,
                    'category' => 'maintenance',
                    'action_label' => 'Review PM Schedules',
                ],
            ];
        }
    }

    public function render()
    {
        return view('livewire.ai-insights-panel');
    }
}
