<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->words(3, true) . ' Project',
            'status' => 'commissioning',
            'total_issues' => 20,
            'open_issues' => 5,
            'total_tests' => 40,
            'completed_tests' => 30,
            'total_closeout_docs' => 15,
            'completed_closeout_docs' => 10,
        ];
    }

    public function highReadiness(): static
    {
        return $this->state([
            'open_issues' => 0,
            'completed_tests' => 40,
            'completed_closeout_docs' => 15,
        ]);
    }

    public function lowReadiness(): static
    {
        return $this->state([
            'open_issues' => 18,
            'completed_tests' => 5,
            'completed_closeout_docs' => 2,
        ]);
    }
}
