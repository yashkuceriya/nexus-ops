<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;

class ProjectList extends Component
{
    public string $statusFilter = '';

    public string $search = '';

    public function getProjectsProperty()
    {
        return Project::query()
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByDesc('updated_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.project-list')
            ->layout('layouts.app', ['title' => 'Projects']);
    }
}
