<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\ChecklistTemplate;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Checklist\PreFunctionalChecklistService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Seeds a library of Pre-Functional Checklist templates (industry-standard
 * L1/L2 readiness items) and then runs a handful of demo completions so
 * the board and scorecard have interesting data on first login.
 *
 * Templates are scoped to each tenant so multi-tenant demo data stays
 * isolated, and completions cover the full outcome spectrum (clean,
 * with-deficiencies, in-progress) so the PDF and dashboard widget
 * exercise every branch.
 */
final class PfcTemplateSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private const LIBRARY = [
        [
            'name' => 'Chiller — Installation Readiness (L1)',
            'cx_level' => 'L1',
            'category' => 'hvac',
            'system_types' => ['chiller'],
            'description' => 'Pre-start visual inspection and installation verification for centrifugal/scroll chillers.',
            'steps' => [
                ['order' => 1, 'title' => 'Unit label & serial match submittal', 'description' => 'Verify nameplate matches approved submittal.', 'type' => 'pass_fail', 'priority' => 'high'],
                ['order' => 2, 'title' => 'Seismic restraints installed', 'description' => 'Confirm isolator & bracing per structural drawing.', 'type' => 'pass_fail', 'priority' => 'critical'],
                ['order' => 3, 'title' => 'Clearances per manufacturer', 'description' => '36" service access on all sides verified.', 'type' => 'pass_fail', 'priority' => 'high'],
                ['order' => 4, 'title' => 'Piping insulated & labeled', 'description' => 'Supply/return insulated, flow arrows & system labels present.', 'type' => 'pass_fail', 'priority' => 'medium'],
                ['order' => 5, 'title' => 'Condensate drain routed', 'description' => 'Indirect drain with air gap confirmed.', 'type' => 'pass_fail', 'priority' => 'medium'],
                ['order' => 6, 'title' => 'Electrical disconnect installed', 'description' => 'Lockable disconnect within sight of unit.', 'type' => 'pass_fail', 'priority' => 'critical'],
            ],
        ],
        [
            'name' => 'AHU — Static Readiness (L2)',
            'cx_level' => 'L2',
            'category' => 'hvac',
            'system_types' => ['ahu', 'air_handler'],
            'description' => 'Pre-startup checks for air-handling units before balancing.',
            'steps' => [
                ['order' => 1, 'title' => 'Filter bank installed & sealed', 'description' => 'All filters seated, no bypass.', 'type' => 'pass_fail', 'priority' => 'high'],
                ['order' => 2, 'title' => 'Dampers stroke freely', 'description' => 'OA/RA/EA dampers operate end-to-end by hand.', 'type' => 'pass_fail', 'priority' => 'high'],
                ['order' => 3, 'title' => 'Belt tension & alignment', 'description' => 'Within 1/64" alignment; deflection per manufacturer.', 'type' => 'pass_fail', 'priority' => 'medium'],
                ['order' => 4, 'title' => 'Drain pan slope & trap', 'description' => 'Trap primed, pan slope to drain visible.', 'type' => 'pass_fail', 'priority' => 'medium'],
                ['order' => 5, 'title' => 'VFD parameters match schedule', 'description' => 'Min/max Hz, ramp rates per sequence.', 'type' => 'pass_fail', 'priority' => 'critical'],
                ['order' => 6, 'title' => 'Sensor placement verified', 'description' => 'SAT, RAT, OAT in correct airstream locations.', 'type' => 'pass_fail', 'priority' => 'high'],
            ],
        ],
        [
            'name' => 'Electrical Panel — Pre-Energisation (L1)',
            'cx_level' => 'L1',
            'category' => 'electrical',
            'system_types' => ['electrical_panel', 'switchgear'],
            'description' => 'Safety & compliance checks before initial energisation of a distribution panel.',
            'steps' => [
                ['order' => 1, 'title' => 'Panel schedule up to date', 'description' => 'Printed schedule inside cover matches terminations.', 'type' => 'pass_fail', 'priority' => 'high'],
                ['order' => 2, 'title' => 'Torque marks present', 'description' => 'Lug connections torqued & witness-marked.', 'type' => 'pass_fail', 'priority' => 'critical'],
                ['order' => 3, 'title' => 'Grounding continuity', 'description' => 'Ground bar bonded; continuity to building ground verified.', 'type' => 'pass_fail', 'priority' => 'critical'],
                ['order' => 4, 'title' => 'Arc-flash label affixed', 'description' => 'Per latest study, correct incident energy printed.', 'type' => 'pass_fail', 'priority' => 'high'],
                ['order' => 5, 'title' => 'Dead-front safely removable', 'description' => 'All fasteners present, no warp or pinch.', 'type' => 'pass_fail', 'priority' => 'medium'],
            ],
        ],
        [
            'name' => 'Fire-Alarm Panel — Pre-Functional (L2)',
            'cx_level' => 'L2',
            'category' => 'fire_safety',
            'system_types' => ['fire_alarm'],
            'description' => 'Pre-functional verification of fire-alarm control panel installation.',
            'steps' => [
                ['order' => 1, 'title' => 'Battery backup installed & dated', 'description' => 'Correct amp-hour rating; install date labeled.', 'type' => 'pass_fail', 'priority' => 'critical'],
                ['order' => 2, 'title' => 'Programming loaded', 'description' => 'As-built programming uploaded and version logged.', 'type' => 'pass_fail', 'priority' => 'high'],
                ['order' => 3, 'title' => 'Remote annunciator location', 'description' => 'AHJ-approved location; wiring verified.', 'type' => 'pass_fail', 'priority' => 'high'],
                ['order' => 4, 'title' => 'Conduit entries sealed', 'description' => 'Fire-rated penetrations sealed per UL listing.', 'type' => 'pass_fail', 'priority' => 'medium'],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (Tenant::all() as $tenant) {
            foreach (self::LIBRARY as $row) {
                ChecklistTemplate::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'name' => $row['name'],
                    ],
                    [
                        'type' => ChecklistTemplate::TYPE_PFC,
                        'category' => $row['category'],
                        'cx_level' => $row['cx_level'],
                        'system_types' => $row['system_types'],
                        'description' => $row['description'],
                        'steps' => $row['steps'],
                        'is_active' => true,
                    ]
                );
            }

            $this->seedDemoCompletions($tenant);
        }
    }

    /**
     * Run a handful of PFCs per tenant against the tenant's existing assets
     * so the board and scorecard are populated on a fresh install.
     */
    private function seedDemoCompletions(Tenant $tenant): void
    {
        $lead = User::where('tenant_id', $tenant->id)->first();
        if ($lead === null) {
            return;
        }

        $service = app(PreFunctionalChecklistService::class);

        Auth::login($lead);

        try {
            $templates = ChecklistTemplate::query()
                ->where('tenant_id', $tenant->id)
                ->pfc()
                ->get()
                ->keyBy(fn (ChecklistTemplate $t) => strtolower(implode(',', $t->system_types ?? [])));

            $assets = Asset::query()
                ->where('tenant_id', $tenant->id)
                ->whereNotNull('project_id')
                ->get();

            $runCount = 0;
            foreach ($assets as $asset) {
                $template = $this->pickTemplateForAsset($templates, $asset);
                if ($template === null || $runCount >= 8) {
                    continue;
                }

                $completion = $service->start($template, $asset, $lead);
                $steps = $template->steps ?? [];
                $outcome = ['clean', 'with_gaps', 'in_progress'][$runCount % 3];

                foreach ($steps as $i => $step) {
                    if ($outcome === 'in_progress' && $i >= max(1, (int) floor(count($steps) / 2))) {
                        break;
                    }

                    $status = match (true) {
                        $outcome === 'with_gaps' && $i === 1 => 'fail',
                        $outcome === 'with_gaps' && $i === 3 && count($steps) > 3 => 'fail',
                        $i === count($steps) - 1 && $outcome !== 'clean' => 'na',
                        default => 'pass',
                    };

                    $service->recordResponse(
                        $completion,
                        (int) ($step['order'] ?? ($i + 1)),
                        $status,
                        null,
                        $status === 'fail' ? 'Corrective work order issued to contractor.' : null,
                    );
                }

                if ($outcome !== 'in_progress') {
                    $service->complete($completion->refresh(), $lead);
                }

                $runCount++;
            }
        } finally {
            Auth::logout();
        }
    }

    /**
     * @param  Collection<string, ChecklistTemplate>  $templates
     */
    private function pickTemplateForAsset($templates, Asset $asset): ?ChecklistTemplate
    {
        $type = strtolower((string) $asset->system_type);
        foreach ($templates as $key => $tpl) {
            if ($key === '') {
                continue;
            }
            foreach (explode(',', $key) as $systemType) {
                if ($systemType !== '' && str_contains($type, $systemType)) {
                    return $tpl;
                }
            }
        }

        return null;
    }
}
