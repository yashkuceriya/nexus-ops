<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Services\Turnover\TurnoverPackageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Streams a project's Accelerated Turnover Package as a PDF download.
 *
 * Every generation is logged to the audit trail so owners can prove, after a
 * dispute or compliance audit, exactly which revision of the package was
 * handed over on which date and by whom.
 */
final class TurnoverPackageController extends Controller
{
    public function __construct(
        private readonly TurnoverPackageService $generator,
    ) {}

    public function download(Request $request, int $projectId): Response
    {
        $tenantId = $request->user()->tenant_id;

        $project = Project::where('tenant_id', $tenantId)->findOrFail($projectId);

        $this->authorize('view', $project);

        $pdf = $this->generator->render($project);
        $filename = $this->generator->filenameFor($project);

        AuditLog::record(
            action: 'turnover_package_generated',
            model: $project,
            newValues: [
                'filename' => $filename,
                'readiness_score' => $project->calculateReadinessScore(),
                'generated_by' => $request->user()?->id,
                'generated_at' => now()->toIso8601String(),
            ],
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => (string) strlen($pdf),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
