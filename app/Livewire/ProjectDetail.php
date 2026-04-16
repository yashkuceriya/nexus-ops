<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;

class ProjectDetail extends Component
{
    public Project $project;

    public function mount(int $id): void
    {
        $this->project = Project::findOrFail($id);
    }

    public function getBlockersProperty(): array
    {
        return $this->project->getHandoverBlockers();
    }

    public function getIssuesProperty()
    {
        return $this->project->issues()
            ->with(['assignee', 'asset'])
            ->orderByRaw("CASE status WHEN 'open' THEN 1 WHEN 'in_progress' THEN 2 WHEN 'work_completed' THEN 3 WHEN 'closed' THEN 4 WHEN 'deferred' THEN 5 WHEN 'draft' THEN 6 ELSE 7 END")
            ->get();
    }

    public function getAssetsProperty()
    {
        return $this->project->assets()
            ->with('location')
            ->orderBy('system_type')
            ->get();
    }

    public function getCloseoutProperty()
    {
        return $this->project->closeoutRequirements()
            ->with('asset')
            ->orderByRaw("CASE status WHEN 'required' THEN 1 WHEN 'rejected' THEN 2 WHEN 'submitted' THEN 3 WHEN 'approved' THEN 4 ELSE 5 END")
            ->get();
    }

    public function render()
    {
        return view('livewire.project-detail')
            ->layout('layouts.app', ['title' => $this->project->name]);
    }
}
