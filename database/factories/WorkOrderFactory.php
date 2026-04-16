<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Tenant;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'project_id' => Project::factory(),
            'wo_number' => 'WO-' . now()->format('Ym') . '-' . str_pad((string) fake()->unique()->randomNumber(4), 4, '0', STR_PAD_LEFT),
            'title' => fake()->sentence(4),
            'status' => 'pending',
            'priority' => 'medium',
            'type' => 'corrective',
            'source' => 'manual',
            'sla_hours' => 24,
            'sla_deadline' => now()->addHours(24),
        ];
    }

    public function overdue(): static
    {
        return $this->state([
            'sla_deadline' => now()->subHours(2),
        ]);
    }

    public function slaBreached(): static
    {
        return $this->state([
            'sla_breached' => true,
            'sla_deadline' => now()->subHours(4),
        ]);
    }

    public function emergency(): static
    {
        return $this->state([
            'priority' => 'emergency',
            'sla_hours' => 2,
            'sla_deadline' => now()->addHours(2),
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'started_at' => now()->subHours(3),
            'completed_at' => now()->subHour(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }
}
