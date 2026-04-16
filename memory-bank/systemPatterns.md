# System Patterns — NexusOps

## Architecture
Modular monolith with clean separation:
- **Models** (app/Models/) — 21 Eloquent models with tenant_id scoping
- **Services** (app/Services/) — business logic layer (WorkOrderService, SensorIngestService, AutomationEngine, AssetHealthService, InsightsGenerator, DashboardService)
- **Livewire Components** (app/Livewire/) — 30 reactive UI components
- **API Controllers** (app/Http/Controllers/Api/) — REST API with Sanctum auth
- **Events/Listeners/Jobs** — async processing (SyncFacilityGridData, CreateWorkOrderFromIssue)

## Multi-Tenancy
- All models have `tenant_id` FK
- All queries scoped via `auth()->user()->tenant_id`
- `EnsureTenantActive` middleware blocks deactivated tenants
- Status mappings are per-tenant (configurable cross-system status translation)

## Key Design Patterns

### Work Order Lifecycle
Status machine: `pending → assigned → in_progress → on_hold → completed → verified`
- Cancellation allowed from most states
- SLA auto-calculated by priority: emergency=2h, critical=4h, high=8h, medium=24h, low=48h
- Sources: manual, issue import, sensor alert, PM schedule, occupant request
- AuditLog::record() on every mutation

### Sensor Pipeline
`SensorIngest → threshold check → anomaly flag → debounce (1hr) → auto work order → notification`

### Automation Engine
Trigger types: work_order_created, status_changed, sla_approaching, sla_breached, sensor_alert, issue_imported, pm_due
Actions: assign_to_user, change_priority, send_notification, create_work_order, escalate_to_manager
Conditions: field/operator/value with AND logic

### Asset Health Scoring
Weighted algorithm (no ML): age factor (20%), condition (25%), WO frequency (25%), sensor anomalies (15%), PM compliance (15%)

## UI Patterns
- Dark sidebar (#1a1f2e) with emerald accent (#10b981)
- Livewire wire:poll for real-time feel (5s sensors, 10s dashboard, 15s work orders)
- Alpine.js for modals, tabs, dropdowns, command palette
- Chart.js for analytics, inline SVG sparklines for KPIs
- Toast notifications for user feedback
- Skeleton loaders during Livewire loading states
- CMD+K command palette for global search

## File Naming Conventions
- Livewire: `app/Livewire/WorkOrderDetail.php` → `resources/views/livewire/work-order-detail.blade.php`
- Services: `app/Services/{Domain}/{ServiceName}.php`
- Notifications: `app/Notifications/{NotificationName}.php`
- Policies: `app/Policies/{ModelName}Policy.php`
