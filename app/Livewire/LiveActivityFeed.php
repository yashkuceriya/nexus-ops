<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\OccupantRequest;
use Illuminate\Support\Collection;
use Livewire\Component;

class LiveActivityFeed extends Component
{
    public int $limit = 15;

    public function getActivitiesProperty(): Collection
    {
        if (! auth()->user()?->tenant_id) {
            return collect();
        }

        // Pull recent audit log entries
        $auditActivities = AuditLog::with('user:id,name')
            ->orderByDesc('created_at')
            ->limit($this->limit)
            ->get()
            ->map(function (AuditLog $log) {
                return [
                    'id' => 'audit_'.$log->id,
                    'type' => $this->mapAuditAction($log->action),
                    'description' => $this->describeAuditAction($log),
                    'user_name' => $log->user?->name,
                    'user_initial' => $log->user ? strtoupper(substr($log->user->name, 0, 1)) : 'S',
                    'timestamp' => $log->created_at,
                    'time_ago' => $log->created_at->diffForHumans(),
                ];
            });

        // Pull recent occupant requests
        $requestActivities = OccupantRequest::orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function (OccupantRequest $request) {
                return [
                    'id' => 'request_'.$request->id,
                    'type' => 'request_submitted',
                    'description' => "New {$request->category} request submitted by {$request->requester_name}",
                    'user_name' => $request->requester_name,
                    'user_initial' => strtoupper(substr($request->requester_name, 0, 1)),
                    'timestamp' => $request->created_at,
                    'time_ago' => $request->created_at->diffForHumans(),
                ];
            });

        return $auditActivities
            ->merge($requestActivities)
            ->sortByDesc('timestamp')
            ->take($this->limit)
            ->values();
    }

    private function mapAuditAction(string $action): string
    {
        return match ($action) {
            'work_order_created' => 'work_order_created',
            'work_order_status_changed' => $this->isCompletionAction($action) ? 'work_order_completed' : 'work_order_created',
            'work_order_assigned' => 'work_order_created',
            'work_order_updated' => 'work_order_created',
            default => str_contains($action, 'sensor') ? 'sensor_alert' : 'pm_generated',
        };
    }

    private function isCompletionAction(string $action): bool
    {
        return $action === 'work_order_status_changed';
    }

    private function describeAuditAction(AuditLog $log): string
    {
        $userName = $log->user?->name ?? 'System';
        $newValues = $log->new_values ?? [];

        return match ($log->action) {
            'work_order_created' => "{$userName} created a new work order".(isset($newValues['source']) ? " (source: {$newValues['source']})" : ''),
            'work_order_status_changed' => "{$userName} changed status from ".($log->old_values['status'] ?? '?').' to '.($newValues['status'] ?? '?'),
            'work_order_assigned' => "{$userName} assigned work order to ".($newValues['assignee_name'] ?? 'a technician'),
            'work_order_updated' => "{$userName} updated a work order",
            default => "{$userName} performed {$log->action}",
        };
    }

    public function render()
    {
        return view('livewire.live-activity-feed');
    }
}
