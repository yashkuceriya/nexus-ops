<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AutomationRule;
use App\Models\ChecklistTemplate;
use App\Models\CloseoutRequirement;
use App\Models\Issue;
use App\Models\Location;
use App\Models\MaintenanceSchedule;
use App\Models\Project;
use App\Models\SensorSource;
use App\Models\StatusMapping;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorContract;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(FptScriptSeeder::class);

        $tenant = Tenant::create([
            'name' => 'Acme Facilities Corp',
            'slug' => 'acme-facilities',
            'domain' => 'acme.nexusops.test',
            'external_api_url' => 'https://api.nexusops.internal/v1',
            'external_auth_type' => 'bearer',
            'settings' => ['timezone' => 'America/New_York', 'currency' => 'USD'],
        ]);

        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Sarah Chen',
            'email' => 'admin@acme.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $manager = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'James Rodriguez',
            'email' => 'manager@acme.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $tech1 = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Mike Thompson',
            'email' => 'tech1@acme.com',
            'password' => bcrypt('password'),
            'role' => 'technician',
        ]);

        $tech2 = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Lisa Park',
            'email' => 'tech2@acme.com',
            'password' => bcrypt('password'),
            'role' => 'technician',
        ]);

        // Default status mappings (external system -> Work Orders, mirroring Procore pattern)
        $mappings = [
            ['Draft', 'pending'], ['Open', 'pending'], ['In Progress', 'in_progress'],
            ['Work Completed', 'completed'], ['Closed', 'verified'], ['Deferred', 'on_hold'],
        ];
        foreach ($mappings as [$sourceStatus, $wo]) {
            StatusMapping::create([
                'tenant_id' => $tenant->id,
                'source_system' => 'external',
                'source_entity' => 'issue',
                'source_status' => $sourceStatus,
                'target_entity' => 'work_order',
                'target_status' => $wo,
                'auto_transition' => true,
            ]);
        }

        // Project 1: Data Center (high readiness)
        $dcProject = Project::create([
            'tenant_id' => $tenant->id,
            'external_project_id' => 'EXT-PRJ-1001',
            'name' => 'Meridian Data Center - Phase 2',
            'description' => 'New 50MW data center facility with redundant cooling and power systems',
            'status' => 'closeout',
            'project_type' => 'data_center_cx',
            'address' => '2400 Innovation Drive',
            'city' => 'Ashburn',
            'state' => 'VA',
            'zip' => '20147',
            'total_issues' => 45,
            'open_issues' => 3,
            'total_tests' => 120,
            'completed_tests' => 115,
            'total_closeout_docs' => 30,
            'completed_closeout_docs' => 27,
            'target_handover_date' => now()->addDays(30),
        ]);

        // Project 2: Healthcare (medium readiness)
        $healthProject = Project::create([
            'tenant_id' => $tenant->id,
            'external_project_id' => 'EXT-PRJ-1002',
            'name' => 'St. Mary Medical Center - Wing B Renovation',
            'description' => 'HVAC and electrical systems upgrade for patient wing',
            'status' => 'commissioning',
            'project_type' => 'healthcare',
            'address' => '800 Health Parkway',
            'city' => 'Boston',
            'state' => 'MA',
            'zip' => '02115',
            'total_issues' => 38,
            'open_issues' => 15,
            'total_tests' => 80,
            'completed_tests' => 45,
            'total_closeout_docs' => 25,
            'completed_closeout_docs' => 10,
            'target_handover_date' => now()->addDays(90),
        ]);

        // Project 3: Higher Ed (early stage)
        $eduProject = Project::create([
            'tenant_id' => $tenant->id,
            'external_project_id' => 'EXT-PRJ-1003',
            'name' => 'MIT Research Lab - Building 46',
            'description' => 'New research laboratory with specialized ventilation and fume hoods',
            'status' => 'commissioning',
            'project_type' => 'higher_education',
            'address' => '77 Massachusetts Avenue',
            'city' => 'Cambridge',
            'state' => 'MA',
            'zip' => '02139',
            'total_issues' => 62,
            'open_issues' => 40,
            'total_tests' => 95,
            'completed_tests' => 20,
            'total_closeout_docs' => 35,
            'completed_closeout_docs' => 5,
            'target_handover_date' => now()->addDays(180),
        ]);

        // Update readiness scores
        foreach ([$dcProject, $healthProject, $eduProject] as $project) {
            $project->update(['readiness_score' => $project->calculateReadinessScore()]);
        }

        // Locations for DC project
        $dcBuilding = Location::create([
            'tenant_id' => $tenant->id, 'project_id' => $dcProject->id,
            'name' => 'Data Hall A', 'type' => 'building', 'code' => 'DHA',
        ]);
        $dcMech = Location::create([
            'tenant_id' => $tenant->id, 'project_id' => $dcProject->id,
            'parent_id' => $dcBuilding->id,
            'name' => 'Mechanical Room 1', 'type' => 'mechanical_room', 'code' => 'MR-1',
        ]);
        $dcRoof = Location::create([
            'tenant_id' => $tenant->id, 'project_id' => $dcProject->id,
            'parent_id' => $dcBuilding->id,
            'name' => 'Rooftop Equipment Area', 'type' => 'roof', 'code' => 'ROOF-A',
        ]);

        // Assets for DC project
        $assets = [];
        $assetDefs = [
            ['Chiller Unit CHL-01', 'chiller', 'HVAC', 'Trane', 'CVHF-1000', 'excellent', 'completed', 450000],
            ['Air Handling Unit AHU-03', 'ahu', 'HVAC', 'Carrier', 'AHU-39XL', 'good', 'completed', 85000],
            ['UPS System UPS-A1', 'ups', 'Electrical', 'Eaton', '93PM-200', 'excellent', 'completed', 320000],
            ['CRAC Unit CRAC-07', 'crac', 'HVAC', 'Liebert', 'DSE-175', 'fair', 'in_progress', 120000],
            ['Generator GEN-01', 'generator', 'Electrical', 'Caterpillar', 'C175-16', 'good', 'completed', 750000],
            ['Fire Suppression Panel FSP-01', 'fire_panel', 'Fire/Life Safety', 'Kidde', 'FM-200', 'good', 'completed', 65000],
        ];

        foreach ($assetDefs as $i => [$name, $tag, $system, $mfr, $model, $cond, $cxStatus, $cost]) {
            $loc = $i < 3 ? $dcMech : $dcRoof;
            $asset = Asset::create([
                'tenant_id' => $tenant->id,
                'project_id' => $dcProject->id,
                'location_id' => $loc->id,
                'external_asset_id' => 'EXT-AST-'.(2000 + $i),
                'name' => $name,
                'asset_tag' => strtoupper($tag),
                'qr_code' => 'NXO-'.str_pad($i + 1, 8, '0', STR_PAD_LEFT),
                'category' => $tag,
                'system_type' => $system,
                'manufacturer' => $mfr,
                'model_number' => $model,
                'serial_number' => 'SN-'.Str::random(10),
                'condition' => $cond,
                'commissioning_status' => $cxStatus,
                'install_date' => now()->subMonths(6),
                'warranty_expiry' => now()->addYears(2),
                'replacement_cost' => $cost,
                'expected_life_years' => rand(15, 25),
                'runtime_hours' => rand(500, 4000),
            ]);
            $assets[] = $asset;
        }

        // Child components for equipment hierarchy
        $childDefs = [
            // Chiller CHL-01 (index 0) children
            [$assets[0], 'Compressor-01', 'compressor', 85000],
            [$assets[0], 'Condenser-01', 'condenser', 62000],
            [$assets[0], 'Evaporator-01', 'evaporator', 55000],
            // AHU-03 (index 1) children
            [$assets[1], 'Supply Fan-01', 'fan', 12000],
            [$assets[1], 'VFD-01', 'vfd', 8500],
            [$assets[1], 'Filter Bank-01', 'filter', 3200],
            // Generator GEN-01 (index 4) children
            [$assets[4], 'Engine-01', 'engine', 320000],
            [$assets[4], 'Alternator-01', 'alternator', 185000],
        ];

        foreach ($childDefs as $ci => [$parentAsset, $childName, $childCategory, $childCost]) {
            Asset::create([
                'tenant_id' => $parentAsset->tenant_id,
                'project_id' => $parentAsset->project_id,
                'location_id' => $parentAsset->location_id,
                'parent_asset_id' => $parentAsset->id,
                'name' => $childName,
                'asset_tag' => strtoupper(str_replace(['-', ' '], '_', $childCategory)),
                'qr_code' => 'NXO-'.str_pad(100 + $ci, 8, '0', STR_PAD_LEFT),
                'category' => $childCategory,
                'system_type' => $parentAsset->system_type,
                'manufacturer' => $parentAsset->manufacturer,
                'serial_number' => 'SN-'.Str::random(10),
                'condition' => $parentAsset->condition,
                'commissioning_status' => $parentAsset->commissioning_status,
                'install_date' => $parentAsset->install_date,
                'warranty_expiry' => $parentAsset->warranty_expiry,
                'replacement_cost' => $childCost,
                'expected_life_years' => $parentAsset->expected_life_years,
                'runtime_hours' => $parentAsset->runtime_hours,
            ]);
        }

        // Issues for DC project
        $issueDefs = [
            ['Chiller vibration exceeds threshold', 'critical', 'open', 0],
            ['AHU-03 belt tension out of spec', 'high', 'in_progress', 1],
            ['CRAC-07 humidifier not responding', 'high', 'open', 3],
            ['Generator fuel polishing required', 'medium', 'work_completed', 4],
            ['Fire suppression zone 3 sensor drift', 'medium', 'closed', 5],
        ];

        $issues = [];
        foreach ($issueDefs as $i => [$title, $prio, $status, $assetIdx]) {
            $issue = Issue::create([
                'tenant_id' => $tenant->id,
                'project_id' => $dcProject->id,
                'asset_id' => $assets[$assetIdx]->id,
                'assigned_to' => $i % 2 === 0 ? $tech1->id : $tech2->id,
                'external_issue_id' => 'EXT-ISS-'.(3000 + $i),
                'title' => $title,
                'description' => "Commissioning issue detected during functional performance testing for {$assets[$assetIdx]->name}.",
                'status' => $status,
                'priority' => $prio,
                'issue_type' => ['functional_performance_test_failure', 'design_intent_deviation', 'installation_deficiency'][$i % 3],
                'source_system' => 'external',
                'due_date' => now()->addDays(rand(3, 30)),
                'resolved_at' => in_array($status, ['work_completed', 'closed']) ? now()->subDays(rand(1, 5)) : null,
            ]);
            $issues[] = $issue;
        }

        // Work Orders from issues
        foreach ($issues as $i => $issue) {
            WorkOrder::create([
                'tenant_id' => $tenant->id,
                'project_id' => $dcProject->id,
                'asset_id' => $issue->asset_id,
                'location_id' => $assets[$issueDefs[$i][3]]->location_id,
                'issue_id' => $issue->id,
                'assigned_to' => $issue->assigned_to,
                'created_by' => $admin->id,
                'wo_number' => 'WO-202604-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'title' => $issue->title,
                'description' => $issue->description,
                'status' => match ($issue->status) {
                    'open' => 'assigned',
                    'in_progress' => 'in_progress',
                    'work_completed' => 'completed',
                    'closed' => 'verified',
                    default => 'pending',
                },
                'priority' => $issue->priority === 'critical' ? 'emergency' : $issue->priority,
                'type' => 'corrective',
                'source' => 'external_issue',
                'sla_hours' => match ($issue->priority) {
                    'critical' => 4, 'high' => 8, 'medium' => 24, default => 48,
                },
                'sla_deadline' => now()->addHours(match ($issue->priority) {
                    'critical' => 4, 'high' => 8, 'medium' => 24, default => 48,
                }),
                'started_at' => in_array($issue->status, ['in_progress', 'work_completed', 'closed']) ? now()->subHours(rand(1, 12)) : null,
                'completed_at' => in_array($issue->status, ['work_completed', 'closed']) ? now()->subHours(rand(1, 4)) : null,
            ]);
        }

        // Preventive maintenance schedules
        foreach (array_slice($assets, 0, 4) as $asset) {
            MaintenanceSchedule::create([
                'tenant_id' => $tenant->id,
                'asset_id' => $asset->id,
                'name' => "PM - {$asset->name} Quarterly Inspection",
                'description' => "Routine quarterly inspection and preventive maintenance for {$asset->name}",
                'frequency' => 'quarterly',
                'trigger_type' => 'calendar',
                'next_due_date' => now()->addDays(rand(1, 90)),
                'estimated_duration_minutes' => rand(60, 240),
                'checklist' => [
                    'Inspect belts and bearings',
                    'Check refrigerant levels',
                    'Verify control sequences',
                    'Clean filters and coils',
                    'Record operational parameters',
                ],
                'is_active' => true,
            ]);
        }

        // Sensor sources on key assets
        $sensorDefs = [
            [$assets[0], 'temperature', 'Supply Water Temp', '°F', 38, 48, 42.5],
            [$assets[0], 'vibration', 'Compressor Vibration', 'mm/s', null, 4.5, 5.2],
            [$assets[1], 'temperature', 'Discharge Air Temp', '°F', 52, 58, 55.0],
            [$assets[1], 'pressure', 'Filter DP', 'inWC', null, 1.5, 0.8],
            [$assets[3], 'humidity', 'Room Humidity', '%RH', 40, 60, 55.0],
            [$assets[3], 'temperature', 'Room Temperature', '°F', 65, 80, 72.0],
        ];

        foreach ($sensorDefs as [$asset, $type, $name, $unit, $min, $max, $lastVal]) {
            $sensor = SensorSource::create([
                'tenant_id' => $tenant->id,
                'asset_id' => $asset->id,
                'location_id' => $asset->location_id,
                'name' => $name,
                'sensor_type' => $type,
                'unit' => $unit,
                'threshold_min' => $min,
                'threshold_max' => $max,
                'last_value' => $lastVal,
                'last_reading_at' => now()->subMinutes(rand(1, 30)),
                'alert_enabled' => true,
                'is_active' => true,
            ]);

            // Generate 24h of readings (every 15 min = 96 readings)
            for ($h = 96; $h >= 0; $h--) {
                $baseVal = (float) $lastVal;
                $noise = (rand(-100, 100) / 100) * ($max ? ($max - ($min ?? 0)) * 0.05 : 1);
                $val = round($baseVal + $noise, 2);
                $isAnomaly = $sensor->isValueOutOfRange($val);

                $sensor->readings()->create([
                    'value' => $val,
                    'is_anomaly' => $isAnomaly,
                    'anomaly_type' => $isAnomaly ? $sensor->getAnomalyType($val) : null,
                    'recorded_at' => now()->subMinutes($h * 15),
                ]);
            }
        }

        // Automation rules
        AutomationRule::create([
            'tenant_id' => $tenant->id,
            'name' => 'Auto-escalate critical HVAC issues',
            'description' => 'Automatically escalates to management when a critical HVAC work order is created.',
            'is_active' => true,
            'trigger_type' => 'work_order_created',
            'conditions' => [
                ['field' => 'priority', 'operator' => 'equals', 'value' => 'critical'],
                ['field' => 'system_type', 'operator' => 'equals', 'value' => 'HVAC'],
            ],
            'actions' => [
                ['type' => 'escalate_to_manager', 'message' => 'Critical HVAC issue requires immediate manager attention.'],
            ],
            'execution_count' => 3,
            'last_executed_at' => now()->subHours(6),
        ]);

        AutomationRule::create([
            'tenant_id' => $tenant->id,
            'name' => 'Reassign emergency sensor alerts',
            'description' => 'Automatically assigns sensor-triggered work orders to the primary technician.',
            'is_active' => true,
            'trigger_type' => 'sensor_alert',
            'conditions' => [],
            'actions' => [
                ['type' => 'assign_to_user', 'user_id' => $tech1->id],
            ],
            'execution_count' => 7,
            'last_executed_at' => now()->subHours(2),
        ]);

        AutomationRule::create([
            'tenant_id' => $tenant->id,
            'name' => 'Notify on SLA breach',
            'description' => 'Sends notifications and escalates priority when an SLA is breached.',
            'is_active' => true,
            'trigger_type' => 'sla_breached',
            'conditions' => [],
            'actions' => [
                ['type' => 'send_notification', 'channel' => 'email', 'message' => 'SLA has been breached for this work order. Immediate action required.'],
                ['type' => 'change_priority', 'priority' => 'emergency'],
            ],
            'execution_count' => 1,
            'last_executed_at' => now()->subDay(),
        ]);

        // Vendors
        $vendor1 = Vendor::create([
            'tenant_id' => $tenant->id,
            'name' => 'ThermalTech HVAC Services',
            'contact_name' => 'Robert Daniels',
            'email' => 'rdaniels@thermaltech.com',
            'phone' => '(703) 555-0142',
            'address' => '1200 Industrial Blvd',
            'city' => 'Sterling',
            'state' => 'VA',
            'zip' => '20166',
            'trade_specialties' => ['HVAC'],
            'insurance_expiry' => now()->addMonths(8),
            'license_number' => 'HVAC-VA-2024-8891',
            'is_active' => true,
            'avg_response_hours' => 2.5,
            'avg_completion_hours' => 18.0,
            'total_work_orders' => 34,
            'total_spend' => 127500.00,
            'rating' => 4.2,
            'notes' => 'Preferred HVAC vendor for data center projects. Excellent response time for emergency calls.',
        ]);

        VendorContract::create([
            'vendor_id' => $vendor1->id,
            'tenant_id' => $tenant->id,
            'title' => 'HVAC Maintenance & Emergency Service Agreement',
            'contract_number' => 'CTR-2026-001',
            'start_date' => now()->subMonths(3),
            'end_date' => now()->addMonths(9),
            'auto_renew' => true,
            'monthly_cost' => 4500.00,
            'annual_value' => 54000.00,
            'nte_limit' => 5000.00,
            'scope' => 'Quarterly preventive maintenance on all HVAC units, 24/7 emergency response, parts and labor included up to NTE per work order.',
            'status' => 'active',
        ]);

        $vendor2 = Vendor::create([
            'tenant_id' => $tenant->id,
            'name' => 'PowerLine Electrical',
            'contact_name' => 'Maria Vasquez',
            'email' => 'mvasquez@powerlineelec.com',
            'phone' => '(617) 555-0289',
            'address' => '88 Circuit Avenue',
            'city' => 'Boston',
            'state' => 'MA',
            'zip' => '02134',
            'trade_specialties' => ['Electrical'],
            'insurance_expiry' => now()->addDays(15),
            'license_number' => 'ELEC-MA-2024-4472',
            'is_active' => true,
            'avg_response_hours' => 4.0,
            'avg_completion_hours' => 24.0,
            'total_work_orders' => 18,
            'total_spend' => 68200.00,
            'rating' => 3.8,
            'notes' => 'Reliable electrical contractor. Insurance renewal coming up soon.',
        ]);

        $vendor3 = Vendor::create([
            'tenant_id' => $tenant->id,
            'name' => 'AllServ Facility Maintenance',
            'contact_name' => 'Tom Hartwell',
            'email' => 'thartwell@allserv.com',
            'phone' => '(571) 555-0367',
            'address' => '450 Service Road',
            'city' => 'Ashburn',
            'state' => 'VA',
            'zip' => '20147',
            'trade_specialties' => ['General Maintenance', 'HVAC', 'Plumbing', 'Electrical'],
            'insurance_expiry' => now()->addMonths(11),
            'license_number' => 'GC-VA-2024-1156',
            'is_active' => true,
            'avg_response_hours' => 1.5,
            'avg_completion_hours' => 12.0,
            'total_work_orders' => 67,
            'total_spend' => 245800.00,
            'rating' => 4.5,
            'notes' => 'Full-service facility maintenance provider. Handles overflow work across all trades. Top-rated vendor.',
        ]);

        VendorContract::create([
            'vendor_id' => $vendor3->id,
            'tenant_id' => $tenant->id,
            'title' => 'Comprehensive Facility Maintenance Contract',
            'contract_number' => 'CTR-2026-003',
            'start_date' => now()->subMonths(6),
            'end_date' => now()->addMonths(6),
            'auto_renew' => true,
            'monthly_cost' => 8500.00,
            'annual_value' => 102000.00,
            'nte_limit' => 2500.00,
            'scope' => 'General maintenance, HVAC support, plumbing, and electrical services for all managed facilities. NTE of $2,500 per work order without additional approval.',
            'status' => 'active',
        ]);

        // Assign some work orders to vendors
        $workOrders = WorkOrder::where('tenant_id', $tenant->id)->get();
        if ($workOrders->count() >= 3) {
            $workOrders[0]->update(['vendor_id' => $vendor1->id]);
            $workOrders[1]->update(['vendor_id' => $vendor3->id]);
            $workOrders[3]->update(['vendor_id' => $vendor2->id]);
        }

        // Closeout requirements for DC project
        $docCategories = ['om_manual', 'warranty', 'as_built', 'test_report', 'training_record', 'certification'];
        foreach ($assets as $i => $asset) {
            foreach (array_slice($docCategories, 0, rand(3, 6)) as $cat) {
                CloseoutRequirement::create([
                    'tenant_id' => $tenant->id,
                    'project_id' => $dcProject->id,
                    'asset_id' => $asset->id,
                    'name' => ucwords(str_replace('_', ' ', $cat))." - {$asset->name}",
                    'category' => $cat,
                    'status' => $i < 4 ? 'approved' : (rand(0, 1) ? 'submitted' : 'required'),
                    'due_date' => now()->addDays(rand(10, 60)),
                ]);
            }
        }

        // L1-L5 Commissioning test level closeout requirements
        $cxLevels = [
            ['L1 Factory Witness Test Report', 'test_report'],
            ['L2 Installation Verification', 'certification'],
            ['L3 Component Test Report', 'test_report'],
            ['L4 System Integration Test', 'test_report'],
            ['L5 Integrated Systems Test (IST)', 'test_report'],
        ];

        foreach ($cxLevels as $li => [$cxName, $cxCategory]) {
            CloseoutRequirement::create([
                'tenant_id' => $tenant->id,
                'project_id' => $dcProject->id,
                'asset_id' => $assets[0]->id,
                'name' => $cxName,
                'category' => $cxCategory,
                'status' => $li < 3 ? 'approved' : ($li < 4 ? 'submitted' : 'required'),
                'due_date' => now()->addDays(10 + ($li * 15)),
            ]);
        }

        // L3 UPS Functional Performance Test checklist template
        ChecklistTemplate::create([
            'tenant_id' => $tenant->id,
            'name' => 'L3 UPS Functional Performance Test',
            'description' => 'Level 3 component-level functional performance test for UPS systems per data center commissioning standards.',
            'category' => 'electrical',
            'steps' => [
                ['name' => 'Verify UPS input voltage within tolerance', 'type' => 'numeric', 'unit' => 'V', 'min' => 475, 'max' => 505],
                ['name' => 'Confirm battery backup duration meets design', 'type' => 'pass_fail'],
                ['name' => 'Test transfer to bypass mode', 'type' => 'pass_fail'],
                ['name' => 'Record output THD', 'type' => 'numeric', 'unit' => '%', 'max' => 5],
                ['name' => 'Verify all alarms and notifications', 'type' => 'pass_fail'],
                ['name' => 'Document any deviations from Sequence of Operations (SOO)', 'type' => 'text'],
            ],
            'is_active' => true,
        ]);

        // Seed a realistic set of FPT executions so the Cx Test Matrix,
        // readiness score, and PDF reports all have live data on first boot.
        $this->call(FptDemoExecutionSeeder::class);
        $this->call(PfcTemplateSeeder::class);

        // Refresh readiness scores now that the FPT dimension has data.
        foreach ([$dcProject, $healthProject, $eduProject] as $project) {
            $project->refresh();
            $project->update(['readiness_score' => $project->calculateReadinessScore()]);
        }
    }
}
