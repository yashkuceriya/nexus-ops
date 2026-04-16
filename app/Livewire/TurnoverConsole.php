<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Project;
use App\Services\Turnover\TurnoverPackageService;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The Turnover Console is the stakeholder-facing staging area for a project's
 * handover package. Before anyone downloads the PDF, they land here and see:
 *
 *   - The live readiness score (same calculation as the PDF banner)
 *   - Every handover blocker, rolled up across issues / tests / docs / FPT
 *   - A commissioning snapshot (execution + witness + step pass rates)
 *   - A timeline of previously generated packages (pulled from the audit log)
 *
 * Generating the PDF is a one-click action that records the download to the
 * audit trail and streams the same `TurnoverPackageService` output the bulk
 * download endpoint serves — so every generation is attributable.
 */
class TurnoverConsole extends Component
{
    public Project $project;

    public ?string $shareLink = null;

    public int $shareExpiryDays = 30;

    public function mount(int $projectId): void
    {
        $tenantId = auth()->user()->tenant_id;

        $this->project = Project::where('tenant_id', $tenantId)
            ->with('tenant:id,name')
            ->findOrFail($projectId);
    }

    #[Computed]
    public function payload(): array
    {
        return app(TurnoverPackageService::class)->buildPayload($this->project);
    }

    #[Computed]
    public function history(): array
    {
        return AuditLog::query()
            ->where('tenant_id', $this->project->tenant_id)
            ->where('auditable_type', $this->project->getMorphClass())
            ->where('auditable_id', $this->project->id)
            ->where('action', 'turnover_package_generated')
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'filename' => $log->new_values['filename'] ?? null,
                'readiness_score' => $log->new_values['readiness_score'] ?? null,
                'generated_by' => $log->user?->name,
                'generated_at' => $log->created_at?->format('M d, Y g:i A'),
            ])
            ->all();
    }

    /**
     * Mint a signed, expiring stakeholder share URL. The audit log gets a
     * `turnover_share_created` entry so owners can see when a link was
     * issued and by whom — which also lets them revoke access by rotating
     * the signing key if needed.
     */
    public function generateShareLink(): void
    {
        $days = max(1, min(90, $this->shareExpiryDays));

        $this->shareLink = URL::signedRoute(
            'public.turnover.show',
            ['projectId' => $this->project->id],
            now()->addDays($days),
        );

        AuditLog::record(
            action: 'turnover_share_created',
            model: $this->project,
            newValues: [
                'expires_at' => now()->addDays($days)->toIso8601String(),
                'expiry_days' => $days,
            ],
        );
    }

    public function render()
    {
        return view('livewire.turnover-console')
            ->layout('layouts.app', [
                'title' => 'Turnover Package',
                'subtitle' => $this->project->name,
            ]);
    }
}
