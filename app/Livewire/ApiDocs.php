<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('API Documentation')]
class ApiDocs extends Component
{
    public string $selectedEndpoint = 'POST /api/v1/auth/login';

    public function getEndpointsProperty(): array
    {
        return [
            'Authentication' => [
                [
                    'method' => 'POST',
                    'path' => '/api/v1/auth/login',
                    'description' => 'Authenticate a user and receive a Bearer token. The token should be included in the Authorization header for all subsequent requests. Tokens are scoped to the user\'s tenant and expire after 24 hours of inactivity.',
                    'parameters' => [
                        ['name' => 'email', 'type' => 'string', 'required' => true, 'description' => 'The user\'s registered email address'],
                        ['name' => 'password', 'type' => 'string', 'required' => true, 'description' => 'The user\'s password'],
                    ],
                    'request' => [
                        'email' => 'operator@nexusops.com',
                        'password' => 'your-secure-password',
                    ],
                    'response' => [
                        'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
                        'token_type' => 'Bearer',
                        'expires_in' => 86400,
                        'user' => [
                            'id' => 1,
                            'name' => 'John Operator',
                            'email' => 'operator@nexusops.com',
                            'role' => 'admin',
                            'tenant_id' => 1,
                        ],
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/v1/auth/me',
                    'description' => 'Retrieve the currently authenticated user\'s profile, including role and tenant information. Requires a valid Bearer token.',
                    'parameters' => [],
                    'request' => null,
                    'response' => [
                        'id' => 1,
                        'name' => 'John Operator',
                        'email' => 'operator@nexusops.com',
                        'role' => 'admin',
                        'tenant_id' => 1,
                        'created_at' => '2026-01-15T08:30:00Z',
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/api/v1/auth/logout',
                    'description' => 'Revoke the current access token, ending the authenticated session. The token will immediately become invalid.',
                    'parameters' => [],
                    'request' => null,
                    'response' => [
                        'message' => 'Successfully logged out',
                    ],
                ],
            ],
            'Dashboard' => [
                [
                    'method' => 'GET',
                    'path' => '/api/v1/dashboard',
                    'description' => 'Retrieve the main dashboard summary including project readiness scores, open work order counts, recent activity, and sensor health overview for the current tenant.',
                    'parameters' => [],
                    'request' => null,
                    'response' => [
                        'projects' => [
                            ['id' => 1, 'name' => 'Data Center Alpha', 'readiness_score' => 87.5, 'open_issues' => 3],
                        ],
                        'work_orders' => ['open' => 12, 'in_progress' => 8, 'completed_today' => 5],
                        'sensor_health' => ['active' => 142, 'anomalies' => 2],
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/v1/dashboard/kpis',
                    'description' => 'Retrieve key performance indicators: open work orders, SLA breach count, mean time to repair (MTTR), PM compliance percentage, active sensor count, and anomaly count.',
                    'parameters' => [],
                    'request' => null,
                    'response' => [
                        'open_work_orders' => 12,
                        'sla_breached' => 2,
                        'mttr_hours' => 4.2,
                        'pm_compliance' => 94.5,
                        'active_sensors' => 142,
                        'anomaly_sensors' => 2,
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/v1/dashboard/sensors',
                    'description' => 'Retrieve a high-level sensor overview with aggregated readings, alert counts, and zone-level summaries for the dashboard sensor widget.',
                    'parameters' => [],
                    'request' => null,
                    'response' => [
                        'total_sensors' => 142,
                        'online' => 138,
                        'offline' => 4,
                        'alerts' => 2,
                        'zones' => [
                            ['name' => 'Server Room A', 'sensors' => 24, 'avg_temp' => 21.3, 'status' => 'normal'],
                            ['name' => 'Server Room B', 'sensors' => 18, 'avg_temp' => 22.1, 'status' => 'warning'],
                        ],
                    ],
                ],
            ],
            'Work Orders' => [
                [
                    'method' => 'GET',
                    'path' => '/api/v1/work-orders',
                    'description' => 'List all work orders for the current tenant with pagination. Supports filtering by status, priority, assignee, and project. Results are ordered by creation date descending.',
                    'parameters' => [
                        ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filter by status: pending, assigned, in_progress, on_hold, completed, verified, cancelled'],
                        ['name' => 'priority', 'type' => 'string', 'required' => false, 'description' => 'Filter by priority: low, medium, high, critical, emergency'],
                        ['name' => 'project_id', 'type' => 'integer', 'required' => false, 'description' => 'Filter by project ID'],
                        ['name' => 'page', 'type' => 'integer', 'required' => false, 'description' => 'Page number for pagination (default: 1)'],
                        ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Items per page (default: 15, max: 100)'],
                    ],
                    'request' => null,
                    'response' => [
                        'data' => [
                            [
                                'id' => 1,
                                'wo_number' => 'WO-2026-0001',
                                'title' => 'HVAC Unit #3 — Compressor Failure',
                                'priority' => 'critical',
                                'status' => 'in_progress',
                                'assigned_to' => 'Mike Torres',
                                'project' => 'Data Center Alpha',
                                'created_at' => '2026-04-10T14:30:00Z',
                            ],
                        ],
                        'meta' => ['current_page' => 1, 'last_page' => 3, 'per_page' => 15, 'total' => 42],
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/api/v1/work-orders',
                    'description' => 'Create a new work order. The work order will be assigned a sequential WO number automatically. If an asset_id is provided, the work order will be linked to that asset for tracking.',
                    'parameters' => [
                        ['name' => 'title', 'type' => 'string', 'required' => true, 'description' => 'Short description of the work to be done'],
                        ['name' => 'description', 'type' => 'string', 'required' => false, 'description' => 'Detailed description of the issue or task'],
                        ['name' => 'priority', 'type' => 'string', 'required' => true, 'description' => 'Priority level: low, medium, high, critical, emergency'],
                        ['name' => 'project_id', 'type' => 'integer', 'required' => true, 'description' => 'The project this work order belongs to'],
                        ['name' => 'asset_id', 'type' => 'integer', 'required' => false, 'description' => 'Optional asset this work order is associated with'],
                        ['name' => 'assigned_to', 'type' => 'integer', 'required' => false, 'description' => 'User ID of the assignee'],
                    ],
                    'request' => [
                        'title' => 'Replace UPS Battery Bank — Rack C4',
                        'description' => 'Battery bank showing degraded capacity. Replace all 12 cells in rack C4.',
                        'priority' => 'high',
                        'project_id' => 1,
                        'asset_id' => 45,
                        'assigned_to' => 7,
                    ],
                    'response' => [
                        'id' => 43,
                        'wo_number' => 'WO-2026-0043',
                        'title' => 'Replace UPS Battery Bank — Rack C4',
                        'status' => 'pending',
                        'priority' => 'high',
                        'created_at' => '2026-04-14T09:15:00Z',
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/v1/work-orders/{id}',
                    'description' => 'Retrieve a single work order by ID, including full details, assignee information, linked asset, checklist items, and activity history.',
                    'parameters' => [
                        ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'The work order ID'],
                    ],
                    'request' => null,
                    'response' => [
                        'id' => 1,
                        'wo_number' => 'WO-2026-0001',
                        'title' => 'HVAC Unit #3 — Compressor Failure',
                        'description' => 'Compressor failure on HVAC unit serving Server Room B. Temperature rising.',
                        'priority' => 'critical',
                        'status' => 'in_progress',
                        'assignee' => ['id' => 7, 'name' => 'Mike Torres'],
                        'project' => ['id' => 1, 'name' => 'Data Center Alpha'],
                        'asset' => ['id' => 12, 'name' => 'HVAC Unit #3', 'qr_code' => 'AST-HVAC-003'],
                        'created_at' => '2026-04-10T14:30:00Z',
                        'updated_at' => '2026-04-12T11:45:00Z',
                    ],
                ],
                [
                    'method' => 'PUT',
                    'path' => '/api/v1/work-orders/{id}',
                    'description' => 'Update an existing work order. All provided fields will be overwritten. Omitted fields remain unchanged. Cannot update completed or cancelled work orders.',
                    'parameters' => [
                        ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'The work order ID'],
                        ['name' => 'title', 'type' => 'string', 'required' => false, 'description' => 'Updated title'],
                        ['name' => 'description', 'type' => 'string', 'required' => false, 'description' => 'Updated description'],
                        ['name' => 'priority', 'type' => 'string', 'required' => false, 'description' => 'Updated priority level'],
                        ['name' => 'assigned_to', 'type' => 'integer', 'required' => false, 'description' => 'Updated assignee user ID'],
                    ],
                    'request' => [
                        'title' => 'HVAC Unit #3 — Compressor Replacement',
                        'priority' => 'emergency',
                        'assigned_to' => 12,
                    ],
                    'response' => [
                        'id' => 1,
                        'wo_number' => 'WO-2026-0001',
                        'title' => 'HVAC Unit #3 — Compressor Replacement',
                        'priority' => 'emergency',
                        'status' => 'in_progress',
                        'updated_at' => '2026-04-14T10:00:00Z',
                    ],
                ],
                [
                    'method' => 'PATCH',
                    'path' => '/api/v1/work-orders/{id}/status',
                    'description' => 'Update the status of a work order. Valid transitions are enforced (e.g., pending -> assigned -> in_progress -> completed -> verified). Invalid transitions return a 422 error.',
                    'parameters' => [
                        ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'The work order ID'],
                        ['name' => 'status', 'type' => 'string', 'required' => true, 'description' => 'New status: pending, assigned, in_progress, on_hold, completed, verified, cancelled'],
                        ['name' => 'notes', 'type' => 'string', 'required' => false, 'description' => 'Optional notes about the status change'],
                    ],
                    'request' => [
                        'status' => 'completed',
                        'notes' => 'Compressor replaced and tested. Temperature nominal.',
                    ],
                    'response' => [
                        'id' => 1,
                        'status' => 'completed',
                        'previous_status' => 'in_progress',
                        'updated_at' => '2026-04-14T16:30:00Z',
                    ],
                ],
            ],
            'Assets' => [
                [
                    'method' => 'GET',
                    'path' => '/api/v1/assets',
                    'description' => 'List all assets for the current tenant with pagination. Supports filtering by type, status, project, and location. Each asset includes its health score and linked sensor count.',
                    'parameters' => [
                        ['name' => 'type', 'type' => 'string', 'required' => false, 'description' => 'Filter by asset type (e.g., hvac, electrical, plumbing, fire_safety)'],
                        ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filter by status: active, maintenance, decommissioned'],
                        ['name' => 'project_id', 'type' => 'integer', 'required' => false, 'description' => 'Filter by project ID'],
                        ['name' => 'page', 'type' => 'integer', 'required' => false, 'description' => 'Page number (default: 1)'],
                    ],
                    'request' => null,
                    'response' => [
                        'data' => [
                            [
                                'id' => 12,
                                'name' => 'HVAC Unit #3',
                                'type' => 'hvac',
                                'qr_code' => 'AST-HVAC-003',
                                'status' => 'active',
                                'health_score' => 72,
                                'location' => 'Building A, Floor 2',
                                'sensor_count' => 4,
                            ],
                        ],
                        'meta' => ['current_page' => 1, 'last_page' => 5, 'per_page' => 15, 'total' => 68],
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/v1/assets/{id}',
                    'description' => 'Retrieve detailed information about a specific asset, including maintenance history, linked sensors, recent readings, and associated work orders.',
                    'parameters' => [
                        ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'The asset ID'],
                    ],
                    'request' => null,
                    'response' => [
                        'id' => 12,
                        'name' => 'HVAC Unit #3',
                        'type' => 'hvac',
                        'qr_code' => 'AST-HVAC-003',
                        'status' => 'active',
                        'health_score' => 72,
                        'location' => 'Building A, Floor 2',
                        'manufacturer' => 'Carrier',
                        'model' => 'WeatherExpert 50XC',
                        'install_date' => '2024-06-15',
                        'sensors' => [
                            ['id' => 1, 'type' => 'temperature', 'last_reading' => 22.4, 'unit' => 'C'],
                        ],
                        'recent_work_orders' => 3,
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/v1/assets/qr/{code}',
                    'description' => 'Look up an asset by its QR code string. Used by mobile apps to scan physical QR labels on equipment and retrieve the linked asset record.',
                    'parameters' => [
                        ['name' => 'code', 'type' => 'string', 'required' => true, 'description' => 'The QR code string printed on the asset label'],
                    ],
                    'request' => null,
                    'response' => [
                        'id' => 12,
                        'name' => 'HVAC Unit #3',
                        'type' => 'hvac',
                        'qr_code' => 'AST-HVAC-003',
                        'status' => 'active',
                        'health_score' => 72,
                        'location' => 'Building A, Floor 2',
                    ],
                ],
            ],
            'Sensors' => [
                [
                    'method' => 'GET',
                    'path' => '/api/v1/sensors',
                    'description' => 'List all IoT sensor sources for the current tenant. Each sensor includes its latest reading, status, and linked asset. Supports filtering by type and alert status.',
                    'parameters' => [
                        ['name' => 'type', 'type' => 'string', 'required' => false, 'description' => 'Filter by sensor type: temperature, humidity, power, vibration, air_quality'],
                        ['name' => 'status', 'type' => 'string', 'required' => false, 'description' => 'Filter by status: online, offline, alert'],
                        ['name' => 'page', 'type' => 'integer', 'required' => false, 'description' => 'Page number (default: 1)'],
                    ],
                    'request' => null,
                    'response' => [
                        'data' => [
                            [
                                'id' => 1,
                                'name' => 'Temp Sensor — Rack A1',
                                'type' => 'temperature',
                                'status' => 'online',
                                'last_reading' => ['value' => 22.4, 'unit' => 'C', 'timestamp' => '2026-04-14T10:30:00Z'],
                                'asset_id' => 12,
                            ],
                        ],
                        'meta' => ['current_page' => 1, 'last_page' => 6, 'per_page' => 25, 'total' => 142],
                    ],
                ],
                [
                    'method' => 'POST',
                    'path' => '/api/v1/sensors/ingest',
                    'description' => 'Ingest sensor readings in bulk. Accepts an array of readings from one or more sensors. Readings are validated, stored, and checked against threshold rules for alert generation.',
                    'parameters' => [
                        ['name' => 'readings', 'type' => 'array', 'required' => true, 'description' => 'Array of sensor reading objects'],
                        ['name' => 'readings[].sensor_id', 'type' => 'integer', 'required' => true, 'description' => 'The sensor source ID'],
                        ['name' => 'readings[].value', 'type' => 'number', 'required' => true, 'description' => 'The reading value'],
                        ['name' => 'readings[].recorded_at', 'type' => 'string', 'required' => false, 'description' => 'ISO 8601 timestamp (defaults to now)'],
                    ],
                    'request' => [
                        'readings' => [
                            ['sensor_id' => 1, 'value' => 22.4, 'recorded_at' => '2026-04-14T10:30:00Z'],
                            ['sensor_id' => 2, 'value' => 45.8, 'recorded_at' => '2026-04-14T10:30:00Z'],
                            ['sensor_id' => 3, 'value' => 18.7, 'recorded_at' => '2026-04-14T10:30:00Z'],
                        ],
                    ],
                    'response' => [
                        'ingested' => 3,
                        'alerts_triggered' => 0,
                        'errors' => [],
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/v1/sensors/{id}/readings',
                    'description' => 'Retrieve historical readings for a specific sensor. Supports time range filtering and aggregation for charting. Returns up to 1000 data points per request.',
                    'parameters' => [
                        ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'The sensor source ID'],
                        ['name' => 'from', 'type' => 'string', 'required' => false, 'description' => 'Start datetime (ISO 8601, defaults to 24h ago)'],
                        ['name' => 'to', 'type' => 'string', 'required' => false, 'description' => 'End datetime (ISO 8601, defaults to now)'],
                        ['name' => 'interval', 'type' => 'string', 'required' => false, 'description' => 'Aggregation interval: 1m, 5m, 15m, 1h, 1d (default: 5m)'],
                    ],
                    'request' => null,
                    'response' => [
                        'sensor_id' => 1,
                        'sensor_name' => 'Temp Sensor — Rack A1',
                        'unit' => 'C',
                        'readings' => [
                            ['timestamp' => '2026-04-14T10:00:00Z', 'value' => 21.8, 'min' => 21.5, 'max' => 22.1],
                            ['timestamp' => '2026-04-14T10:05:00Z', 'value' => 22.1, 'min' => 21.9, 'max' => 22.4],
                            ['timestamp' => '2026-04-14T10:10:00Z', 'value' => 22.4, 'min' => 22.0, 'max' => 22.8],
                        ],
                        'count' => 3,
                    ],
                ],
            ],
            'Sync' => [
                [
                    'method' => 'POST',
                    'path' => '/api/v1/sync/trigger',
                    'description' => 'Trigger a manual data synchronization between NexusOps and connected external systems (BMS, CMMS, ERP). Returns a sync job ID that can be polled for completion status.',
                    'parameters' => [
                        ['name' => 'scope', 'type' => 'string', 'required' => false, 'description' => 'Sync scope: all, assets, work_orders, sensors (default: all)'],
                        ['name' => 'force', 'type' => 'boolean', 'required' => false, 'description' => 'Force full sync even if no changes detected (default: false)'],
                    ],
                    'request' => [
                        'scope' => 'all',
                        'force' => false,
                    ],
                    'response' => [
                        'job_id' => 'sync_2026041409150001',
                        'status' => 'queued',
                        'scope' => 'all',
                        'estimated_duration' => '30s',
                        'triggered_at' => '2026-04-14T09:15:00Z',
                    ],
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/v1/sync/status',
                    'description' => 'Retrieve the status of the most recent sync operation, including progress percentage, records processed, and any errors encountered during synchronization.',
                    'parameters' => [
                        ['name' => 'job_id', 'type' => 'string', 'required' => false, 'description' => 'Specific job ID to check (defaults to latest)'],
                    ],
                    'request' => null,
                    'response' => [
                        'job_id' => 'sync_2026041409150001',
                        'status' => 'completed',
                        'progress' => 100,
                        'records_synced' => 247,
                        'errors' => 0,
                        'started_at' => '2026-04-14T09:15:01Z',
                        'completed_at' => '2026-04-14T09:15:28Z',
                        'duration_seconds' => 27,
                    ],
                ],
            ],
        ];
    }

    public function selectEndpoint(string $key): void
    {
        $this->selectedEndpoint = $key;
    }

    public function getSelectedProperty(): ?array
    {
        foreach ($this->endpoints as $group => $endpoints) {
            foreach ($endpoints as $endpoint) {
                $key = $endpoint['method'] . ' ' . $endpoint['path'];
                if ($key === $this->selectedEndpoint) {
                    return $endpoint;
                }
            }
        }

        return null;
    }

    public function syntaxHighlight(string $json, bool $preEscaped = false): string
    {
        if (!$preEscaped) {
            $json = e($json);
        }

        // Highlight strings (keys and values)
        $json = preg_replace(
            '/(&quot;)([^&]*?)(&quot;)(\s*:)/s',
            '<span class="text-cyan-400">$1$2$3</span>$4',
            $json
        );

        // Highlight string values (after colon)
        $json = preg_replace(
            '/(:\s*)(&quot;)([^&]*?)(&quot;)/s',
            '$1<span class="text-emerald-400">$2$3$4</span>',
            $json
        );

        // Highlight numbers
        $json = preg_replace(
            '/(?<=: )(-?\d+\.?\d*)(?=[,\s\n\r\]}])/s',
            '<span class="text-amber-400">$1</span>',
            $json
        );

        // Highlight booleans and null
        $json = preg_replace(
            '/(?<=: )(true|false|null)(?=[,\s\n\r\]}])/s',
            '<span class="text-purple-400">$1</span>',
            $json
        );

        return $json;
    }

    public function render()
    {
        return view('livewire.api-docs');
    }
}
