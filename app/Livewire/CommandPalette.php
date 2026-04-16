<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Project;
use App\Models\SensorSource;
use App\Models\WorkOrder;
use Livewire\Component;
use Livewire\Attributes\On;

class CommandPalette extends Component
{
    public string $search = '';
    public int $selectedIndex = 0;

    public function updatedSearch(): void
    {
        $this->selectedIndex = 0;
    }

    public function getResultsProperty(): array
    {
        if (strlen($this->search) < 2) {
            return [];
        }

        $query = $this->search;
        $results = [];

        // Work Orders
        $workOrders = WorkOrder::where(function ($q) use ($query) {
                $q->where('wo_number', 'like', "%{$query}%")
                  ->orWhere('title', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($workOrders as $wo) {
            $results[] = [
                'type' => 'Work Orders',
                'icon' => 'clipboard',
                'title' => $wo->wo_number . ' - ' . $wo->title,
                'subtitle' => ucfirst($wo->status ?? 'open'),
                'url' => route('work-orders.show', $wo->id),
                'hint' => 'WO',
            ];
        }

        // Assets
        $assets = Asset::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('qr_code', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($assets as $asset) {
            $results[] = [
                'type' => 'Assets',
                'icon' => 'cube',
                'title' => $asset->name,
                'subtitle' => $asset->category ?? 'Asset',
                'url' => route('assets.show', $asset->id),
                'hint' => 'AST',
            ];
        }

        // Projects
        $projects = Project::where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($projects as $project) {
            $results[] = [
                'type' => 'Projects',
                'icon' => 'folder',
                'title' => $project->name,
                'subtitle' => ucfirst($project->status ?? 'active'),
                'url' => route('projects.show', $project->id),
                'hint' => 'PRJ',
            ];
        }

        // Sensors
        $sensors = SensorSource::where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($sensors as $sensor) {
            $results[] = [
                'type' => 'Sensors',
                'icon' => 'signal',
                'title' => $sensor->name,
                'subtitle' => $sensor->sensor_type ?? 'Sensor',
                'url' => route('sensors.index'),
                'hint' => 'SNS',
            ];
        }

        return $results;
    }

    public function getQuickActionsProperty(): array
    {
        return [
            [
                'icon' => 'plus',
                'title' => 'Create Work Order',
                'subtitle' => 'Open a new work order',
                'url' => route('work-orders.index'),
                'hint' => 'N',
            ],
            [
                'icon' => 'dashboard',
                'title' => 'View Dashboard',
                'subtitle' => 'Go to the portfolio dashboard',
                'url' => route('dashboard'),
                'hint' => 'D',
            ],
            [
                'icon' => 'signal',
                'title' => 'Go to Sensors',
                'subtitle' => 'View IoT sensor dashboard',
                'url' => route('sensors.index'),
                'hint' => 'S',
            ],
        ];
    }

    public function getRecentPagesProperty(): array
    {
        return [
            ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'dashboard'],
            ['title' => 'Work Orders', 'url' => route('work-orders.index'), 'icon' => 'clipboard'],
            ['title' => 'Assets', 'url' => route('assets.index'), 'icon' => 'cube'],
            ['title' => 'Projects', 'url' => route('projects.index'), 'icon' => 'folder'],
            ['title' => 'IoT Sensors', 'url' => route('sensors.index'), 'icon' => 'signal'],
        ];
    }

    public function render()
    {
        return view('livewire.command-palette', [
            'results' => $this->results,
            'quickActions' => $this->quickActions,
            'recentPages' => $this->recentPages,
        ]);
    }
}
