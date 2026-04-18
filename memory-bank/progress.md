# Progress — NexusOps

## What Works

### Core Platform
- [x] Multi-tenant data model (21 models, tenant_id scoping)
- [x] Authentication (Sanctum API + session web)
- [x] Authorization policies (WorkOrder, Project, Asset)
- [x] Tenant middleware (EnsureTenantActive)
- [x] Audit logging (polymorphic, all mutations tracked)
- [x] Notification system (3 types: WO assigned, SLA breach, sensor alert)
- [x] Database seeder with realistic demo data

### Operations
- [x] Dashboard with KPIs, readiness rings, activity feed
- [x] Projects list + detail with readiness scoring, handover blockers
- [x] Work orders full CRUD (create, edit, status transitions, SLA)
- [x] Assets with QR codes, health scores, maintenance history
- [x] Inspection checklists (pass/fail, numeric, text steps)
- [x] Preventive maintenance scheduler (daily cron, auto-generates WOs)

### Monitoring
- [x] IoT sensor dashboard (5s polling, Chart.js, anomaly detection)
- [x] Interactive SVG floor plan with asset pins
- [x] Asset health matrix (scatter plot, danger zone)
- [x] Mapbox 3D facility map (dark theme, pulsing markers, fly-to)

### Management
- [x] Vendor management (CRUD, contracts, scorecards, NTE)
- [x] Analytics & reports (6 charts, date filtering)
- [x] Workflow automation rules (visual builder, 7 triggers, 5 actions)

### Public
- [x] Occupant request portal (no auth, token tracking)
- [x] Request status tracker with satisfaction survey
- [x] SaaS landing page (animated hero, stats, features)

### Developer
- [x] API documentation page (dark theme, syntax highlighting)
- [x] REST API (17 endpoints, Sanctum auth)
- [x] Audit log viewer with old/new diff

### Tech Polish
- [x] CMD+K command palette (fuzzy search)
- [x] Dark mode toggle (localStorage)
- [x] Animated KPI counters + sparklines
- [x] Live data ticker strip
- [x] AI insights panel (simulated)
- [x] Skeleton loaders
- [x] Toast notifications
- [x] Micro-interactions (button press, card hover, fade-in)
- [x] Livewire polling (5s/10s/15s auto-refresh)

### Deployment
- [x] Multi-stage Dockerfile (PHP 8.3-FPM + Nginx)
- [x] docker-compose.yml (app, worker, scheduler, MySQL, Redis)
- [x] ECS task definitions (web + worker, ARM64 Graviton)
- [x] GitHub Actions CI/CD (test + deploy with OIDC)

### Design System (v6)
- [x] Stitch/Linear-inspired light UI, indigo accent, soft lilac canvas
- [x] `docs/DESIGN.md` with token vocabulary and component recipes
- [x] Layout shell: left sidebar + top tab bar + tenant switcher
- [x] Split-screen login with brand panel
- [x] All 43 Livewire views migrated to `.card`/`.chip`/`.btn-primary`/`label-kicker` tokens
- [x] Dark mode removed (single light theme)

### Commissioning Suite (v3–v5)
- [x] FPT engine: TestScript/TestStep/TestExecution/TestStepResult, auto-eval (tolerance/GTE/between), witness signatures + tamper detection, parent-child retest chains, PDF reports
- [x] FPT UI: TestScriptLibrary, TestScriptEditor, TestExecutionRunner, CxTestMatrix
- [x] PFC (Pre-Functional Checklist): extended checklist templates, multi-session resume, auto-opens deficiency Issues on failure
- [x] Turnover Packages: full payload builder, signed public share URLs, PDF with inventory + QR
- [x] Asset Signoff: 4-state workflow, signature hashing, role-based approvers
- [x] Commissioning Analytics: 6-month trend, aging buckets, top-failing scripts
- [x] Deficiency Board: kanban with advance/rewind/claim + audit trail
- [x] Lessons Learned (7 categories) + Closeout Tracker
- [x] Weekly CX Digest command (`cx:weekly-digest`) with dry-run + per-tenant filter
- [x] API tenant guard middleware (`tenant.active.api`) for v1 API

## What Could Be Added
- [ ] Laravel Reverb WebSocket (replace polling)
- [ ] Email notifications for occupant requests
- [ ] Password reset flow
- [ ] User management CRUD (admin panel)
- [ ] Drag-and-drop work order prioritization
- [ ] Mobile bottom nav (PWA)
- [ ] Offline mode with service worker
- [ ] Energy/sustainability dashboard
- [ ] Document upload/management (S3)
- [ ] Integration marketplace UI

## Senior-Level Refactor (completed)
- [x] PHP enums: WorkOrderStatus, Priority (eliminates duplicated constants)
- [x] Value objects: SlaPolicy, ReadinessScore (domain-driven design)
- [x] BelongsToTenant global scope trait (replaces 50+ manual where clauses)
- [x] 4 Architecture Decision Records in docs/adr/
- [x] API versioning (/api/v1/ prefix)
- [x] Soft deletes on 5 core models
- [x] 3 FormRequest validation classes
- [x] 6 model factories with states (.overdue(), .slaBreached(), .emergency())
- [x] Domain unit tests (ReadinessScore, WorkOrderStatus, Priority)
- [x] Workflow integration tests (full lifecycle, tenant isolation)
- [x] preventLazyLoading in dev
- [x] Sanctum token expiration (24h)

## Stats
| Metric | Count |
|--------|-------|
| Models | 27 |
| Livewire Components | 43 |
| Domain Objects | 4 |
| Services | 13+ (incl. TestExecution, Turnover, Signoff, Checklist) |
| Form Requests | 3 |
| Factories | 6 |
| ADRs | 4 |
| Tests | 138 passing (470 assertions, 1 skipped) |
| Pages | 22+ (plus /fpt/*, /turnover/*) |
| PHP files | 129 |
