<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Project;
use App\Services\Turnover\TurnoverPackageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Public, signed-URL stakeholder portal for an accelerated turnover package.
 *
 * Generating a share link does not copy the PDF — it mints a Laravel
 * signed URL that expires in N days. Anyone with the link (GC, owner-rep,
 * AHJ inspector) can preview readiness and download the live PDF without
 * being provisioned into the tenant. Every preview / download is logged
 * to the audit trail with the inspector's IP and user-agent so the chain
 * of custody is provable after handover.
 */
final class PublicTurnoverController extends Controller
{
    public function __construct(
        private readonly TurnoverPackageService $generator,
    ) {}

    /**
     * Render a read-only stakeholder preview of the turnover package.
     *
     * This endpoint is registered with the `signed` middleware so Laravel
     * will 403 if the signature is tampered with or the URL has expired.
     */
    public function show(Request $request, int $projectId): SymfonyResponse
    {
        $project = Project::with('tenant:id,name')->findOrFail($projectId);
        $payload = $this->generator->buildPayload($project);

        AuditLog::record(
            action: 'turnover_share_previewed',
            model: $project,
            newValues: [
                'ip' => $request->ip(),
                'ua' => substr((string) $request->userAgent(), 0, 200),
            ],
        );

        return response()->view('turnover.public', [
            'project' => $project,
            'payload' => $payload,
            'downloadUrl' => URL::signedRoute(
                'public.turnover.download',
                ['projectId' => $project->id],
                now()->addDays(30),
            ),
        ]);
    }

    /**
     * Signed-URL PDF download. Same bytes as the internal route but no
     * auth guard — callers must present a valid signature.
     */
    public function download(Request $request, int $projectId): Response
    {
        $project = Project::findOrFail($projectId);

        $pdf = $this->generator->render($project);
        $filename = $this->generator->filenameFor($project);

        AuditLog::record(
            action: 'turnover_share_downloaded',
            model: $project,
            newValues: [
                'filename' => $filename,
                'ip' => $request->ip(),
                'ua' => substr((string) $request->userAgent(), 0, 200),
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
