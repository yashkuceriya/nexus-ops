<?php

declare(strict_types=1);

namespace App\Services\FacilityGrid;

use App\Events\IssueImported;
use App\Events\ProjectSynced;
use App\Models\Asset;
use App\Models\Issue;
use App\Models\Project;
use App\Models\SyncWatermark;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates incremental sync of FacilityGrid data into the local database.
 *
 * Key behaviours:
 *  - Cursor / watermark-based incremental sync via `SyncWatermark` model.
 *  - Circuit breaker: halts after N consecutive failures to avoid hammering a broken upstream.
 *  - Upserts keyed on `facilitygrid_*_id` columns to ensure idempotent imports.
 *  - Fires domain events for downstream listeners (readiness scores, notifications, etc.).
 */
final class FacilityGridSyncService
{
    private const int CIRCUIT_BREAKER_THRESHOLD = 5;

    private int $consecutiveFailures = 0;

    public function __construct(
        private readonly FacilityGridClient $client,
        private readonly Tenant $tenant,
    ) {}

    /* ------------------------------------------------------------------
     | Public entry points
     | ----------------------------------------------------------------*/

    /**
     * Run a full incremental sync for the tenant: projects -> issues + assets.
     *
     * @throws FacilityGridException When the circuit breaker trips.
     */
    public function syncAll(): void
    {
        $this->consecutiveFailures = 0;

        $projects = $this->syncProjects();

        foreach ($projects as $project) {
            if ($this->circuitOpen()) {
                Log::error('FacilityGrid sync: circuit breaker open, aborting remaining projects.', [
                    'tenant_id' => $this->tenant->id,
                    'consecutive_failures' => $this->consecutiveFailures,
                ]);
                break;
            }

            $this->syncProjectChildren($project);
        }
    }

    /* ------------------------------------------------------------------
     | Project sync
     | ----------------------------------------------------------------*/

    /**
     * Pull projects from FacilityGrid and upsert locally.
     *
     * @return list<Project> The local Project models that were upserted.
     */
    public function syncProjects(): array
    {
        $watermark = $this->getWatermark('projects');
        $params = $this->buildIncrementalParams($watermark);

        try {
            $response = $this->client->getProjects($params);
            $this->recordSuccess();
        } catch (FacilityGridException $e) {
            $this->recordFailure($e, 'projects');

            return [];
        }

        $localProjects = [];

        foreach ($response['data'] as $row) {
            $project = Project::upsert(
                [
                    [
                        'tenant_id' => $this->tenant->id,
                        'facilitygrid_project_id' => $row['id'],
                        'name' => $row['name'] ?? '',
                        'description' => $row['description'] ?? null,
                        'status' => $row['status'] ?? 'unknown',
                        'metadata' => json_encode($row['metadata'] ?? []),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                ],
                uniqueBy: ['tenant_id', 'facilitygrid_project_id'],
                update: ['name', 'description', 'status', 'metadata', 'updated_at'],
            );

            $localProjects[] = Project::where('tenant_id', $this->tenant->id)
                ->where('facilitygrid_project_id', $row['id'])
                ->first();
        }

        $this->advanceWatermark($watermark, $response['meta']);

        return array_filter($localProjects);
    }

    /* ------------------------------------------------------------------
     | Children sync (issues + assets)
     | ----------------------------------------------------------------*/

    private function syncProjectChildren(Project $project): void
    {
        $issuesImported = $this->syncIssues($project);
        $assetsImported = $this->syncAssets($project);

        $this->updateReadinessScore($project);

        ProjectSynced::dispatch(
            tenantId: $this->tenant->id,
            projectId: $project->id,
            issuesImported: $issuesImported,
            assetsImported: $assetsImported,
        );
    }

    private function syncIssues(Project $project): int
    {
        $watermark = $this->getWatermark("issues:{$project->facilitygrid_project_id}");
        $params = $this->buildIncrementalParams($watermark);

        try {
            $response = $this->client->getIssues($project->facilitygrid_project_id, $params);
            $this->recordSuccess();
        } catch (FacilityGridException $e) {
            $this->recordFailure($e, "issues for project {$project->id}");

            return 0;
        }

        $count = 0;

        foreach ($response['data'] as $row) {
            $wasCreated = ! Issue::where('tenant_id', $this->tenant->id)
                ->where('facilitygrid_issue_id', $row['id'])
                ->exists();

            Issue::upsert(
                [
                    [
                        'tenant_id' => $this->tenant->id,
                        'project_id' => $project->id,
                        'facilitygrid_issue_id' => $row['id'],
                        'title' => $row['title'] ?? '',
                        'description' => $row['description'] ?? null,
                        'severity' => $row['severity'] ?? 'medium',
                        'status' => $row['status'] ?? 'open',
                        'metadata' => json_encode($row['metadata'] ?? []),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                ],
                uniqueBy: ['tenant_id', 'facilitygrid_issue_id'],
                update: ['title', 'description', 'severity', 'status', 'metadata', 'updated_at'],
            );

            $issue = Issue::where('tenant_id', $this->tenant->id)
                ->where('facilitygrid_issue_id', $row['id'])
                ->first();

            if ($issue) {
                IssueImported::dispatch(
                    tenantId: $this->tenant->id,
                    issueId: $issue->id,
                    facilityGridIssueId: $row['id'],
                    wasCreated: $wasCreated,
                );
            }

            $count++;
        }

        $this->advanceWatermark($watermark, $response['meta']);

        return $count;
    }

    private function syncAssets(Project $project): int
    {
        $watermark = $this->getWatermark("assets:{$project->facilitygrid_project_id}");
        $params = $this->buildIncrementalParams($watermark);

        try {
            $response = $this->client->getAssets($project->facilitygrid_project_id, $params);
            $this->recordSuccess();
        } catch (FacilityGridException $e) {
            $this->recordFailure($e, "assets for project {$project->id}");

            return 0;
        }

        $count = 0;

        foreach ($response['data'] as $row) {
            Asset::upsert(
                [
                    [
                        'tenant_id' => $this->tenant->id,
                        'project_id' => $project->id,
                        'facilitygrid_asset_id' => $row['id'],
                        'name' => $row['name'] ?? '',
                        'type' => $row['type'] ?? 'unknown',
                        'status' => $row['status'] ?? 'active',
                        'metadata' => json_encode($row['metadata'] ?? []),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                ],
                uniqueBy: ['tenant_id', 'facilitygrid_asset_id'],
                update: ['name', 'type', 'status', 'metadata', 'updated_at'],
            );

            $count++;
        }

        $this->advanceWatermark($watermark, $response['meta']);

        return $count;
    }

    /* ------------------------------------------------------------------
     | Watermark helpers
     | ----------------------------------------------------------------*/

    private function getWatermark(string $entity): SyncWatermark
    {
        return SyncWatermark::firstOrCreate(
            [
                'tenant_id' => $this->tenant->id,
                'entity' => $entity,
            ],
            [
                'cursor' => null,
                'last_synced_at' => null,
            ],
        );
    }

    private function buildIncrementalParams(SyncWatermark $watermark): array
    {
        $params = [];

        if ($watermark->cursor !== null) {
            $params['cursor'] = $watermark->cursor;
        }

        if ($watermark->last_synced_at !== null) {
            $params['updated_since'] = $watermark->last_synced_at->toIso8601String();
        }

        return $params;
    }

    private function advanceWatermark(SyncWatermark $watermark, array $meta): void
    {
        $watermark->update([
            'cursor' => $meta['next_cursor'] ?? null,
            'last_synced_at' => Carbon::now(),
        ]);
    }

    /* ------------------------------------------------------------------
     | Circuit breaker
     | ----------------------------------------------------------------*/

    private function circuitOpen(): bool
    {
        return $this->consecutiveFailures >= self::CIRCUIT_BREAKER_THRESHOLD;
    }

    private function recordSuccess(): void
    {
        $this->consecutiveFailures = 0;
    }

    private function recordFailure(FacilityGridException $e, string $context): void
    {
        $this->consecutiveFailures++;

        Log::warning("FacilityGrid sync failure [{$context}]", [
            'tenant_id' => $this->tenant->id,
            'error_type' => $e->errorType,
            'status' => $e->status,
            'detail' => $e->detail,
            'consecutive_failures' => $this->consecutiveFailures,
        ]);
    }

    /* ------------------------------------------------------------------
     | Readiness score
     | ----------------------------------------------------------------*/

    /**
     * Recalculate the project readiness score based on synced data.
     *
     * Readiness = (closed issues / total issues) * 100, bounded [0, 100].
     * Projects with zero issues default to a score of 0.
     */
    private function updateReadinessScore(Project $project): void
    {
        $totalIssues = Issue::where('project_id', $project->id)->count();
        $closedIssues = Issue::where('project_id', $project->id)
            ->where('status', 'closed')
            ->count();

        $score = $totalIssues > 0
            ? (int) round(($closedIssues / $totalIssues) * 100)
            : 0;

        $project->update(['readiness_score' => $score]);
    }
}
