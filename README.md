<div align="center">

# NexusOps

**A commissioning and operations platform for high-stakes facilities — data centers, hospitals, research labs.**

[![Live demo](https://img.shields.io/badge/live%20demo-nexusops.up.railway.app-4F46E5?style=for-the-badge)](https://REPLACE_WITH_RAILWAY_URL)
[![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![Livewire 4.2](https://img.shields.io/badge/Livewire-4.2-4E56A6?style=flat)](https://livewire.laravel.com)
[![PHP 8.5](https://img.shields.io/badge/PHP-8.5-777BB4?style=flat&logo=php)](https://www.php.net)
[![Tests](https://img.shields.io/badge/tests-141%20passing-10B981?style=flat)](./tests)

</div>

> Login: `admin@acme.com` / `password`

NexusOps takes a commissioning project through the final 20% — Functional Performance Tests, Pre-Functional Checklists, deficiency resolution, turnover packages — and hands it off to day-one operations with work orders, IoT telemetry, and a living audit trail. Multi-tenant. Role-aware. Offline-tolerant where it matters.

---

## Screenshots

| | |
|---|---|
| ![Dashboard](docs/screenshots/dashboard.png) | ![FPT Runner](docs/screenshots/fpt-runner.png) |
| **Portfolio dashboard** — KPI sparklines, velocity chart, deficiency donut, readiness heatmap | **FPT runner** — witness signature pad, auto-eval preview, retest chaining |
| ![Turnover Console](docs/screenshots/turnover.png) | ![Deficiency Board](docs/screenshots/deficiencies.png) |
| **Turnover console** — animated readiness ring, blockers, signed stakeholder share | **Deficiency board** — kanban with state machine + audit trail |
| ![Sensor Heatmap](docs/screenshots/sensors.png) | ![Login](docs/screenshots/login.png) |
| **Anomaly heatmap** — 7d × 24h density across sensors | **Login** — split-screen, operational stats panel |

---

## What this project demonstrates

- **Domain-driven design.** Value objects (`SlaPolicy`, `ReadinessScore`), PHP 8 enums (`WorkOrderStatus`, `Priority`), and a real state machine on work orders — not string comparisons scattered through controllers.
- **Multi-tenancy done properly.** `BelongsToTenant` global scope trait on every model, `EnsureTenantActive` middleware for web, `EnsureTenantActiveApi` for JSON APIs, and cross-tenant isolation covered by integration tests.
- **Commissioning as code.** FPT engine with auto-evaluation modes (tolerance / GTE / between), witness signature + tamper detection, parent-child retest chains, and a signed public URL for stakeholder preview.
- **Reactive UI without a SPA.** Livewire 4.2 components with `wire:poll` for the IoT dashboard, Alpine for dropdowns/modals, CMD+K command palette, and SVG-based visualizations (sparklines, donuts, rings, heatmap) — no React, no build step.
- **Production shape.** Sanctum-authenticated REST API under `/api/v1`, PDF generation (turnover package + FPT reports), notifications (in-app + email), scheduled jobs (`cx:weekly-digest`, `pm:generate`), audit log with old/new diffs, and role-based policies.
- **Ops-ready.** Driver-agnostic SQL (SQLite in dev, Postgres/MySQL in prod), Docker multi-stage build, ECS task definitions in `deploy/`, Railway one-click deploy, GitHub Actions CI stub.

---

## Stack

| Layer | Tech |
|---|---|
| Backend | Laravel 13, PHP 8.5 |
| UI | Livewire 4.2, Alpine.js, Tailwind CSS |
| Charts | Chart.js (velocity trend) + inline SVG (sparklines, donut, ring, heatmap) |
| Data | SQLite (dev), Postgres/MySQL (prod), driver-agnostic migrations |
| Auth | Sanctum (API, 24h tokens), session (web), 5 role levels |
| Jobs | Laravel queue + scheduler (`cx:weekly-digest` weekly, `pm:generate` daily) |
| PDFs | dompdf (turnover packages, FPT reports) |
| Deploy | Docker, Railway (recommended), AWS Fargate (configs in `deploy/`) |

---

## Architecture

```
┌───────────────────────────────────────────────────────────────────┐
│                          Browser (Livewire)                        │
│    ⌘K palette · sidebar · top tabs · 43 reactive components        │
└───────────────▲───────────────────────────────────────▲────────────┘
                │ Livewire XHR                  session │ Sanctum
                │                                       │
┌───────────────┴───────────────────────────────────────┴────────────┐
│                          Laravel 13 app                            │
│  ┌─────────────┐  ┌──────────────┐  ┌─────────────────────────┐    │
│  │ Livewire    │  │ REST API v1  │  │ Domain                  │    │
│  │ (web)       │  │ (/api/v1/*)  │  │   Enums, Value Objects  │    │
│  └──────┬──────┘  └──────┬───────┘  └──────────────┬──────────┘    │
│         │                │                         │               │
│  ┌──────▼────────────────▼─────────────────────────▼─────────┐     │
│  │ Services: WorkOrder, TestExecution, Turnover, Signoff,    │     │
│  │           Checklist, Sensor, Automation, ReportPdf, ...   │     │
│  └───┬────────────────────┬────────────────────┬──────────────┘    │
│      │ tenant scope       │ events             │ policies          │
│  ┌───▼──────────┐  ┌──────▼──────────┐  ┌──────▼───────────┐       │
│  │ Eloquent     │  │ Queue (db/redis)│  │ Scheduler        │       │
│  │ 27 models,   │  │ notifications,  │  │ pm:generate,     │       │
│  │ soft-delete, │  │ digests,        │  │ cx:weekly-digest │       │
│  │ audit log    │  │ sensor ingest   │  │                  │       │
│  └──────────────┘  └─────────────────┘  └──────────────────┘       │
└────────────────────────────────────────────────────────────────────┘
```

---

## Features

<details>
<summary><b>Commissioning</b></summary>

- FPT engine with test scripts, executions, witness signatures, tamper detection
- Auto-evaluation rules (tolerance, GTE, between) with pass/fail preview on input
- Retest chains linked to parent failed executions
- Pre-Functional Checklists (PFC) with multi-session resume, auto-opens deficiencies on fail
- Turnover packages with PDF, QR codes, signed public stakeholder URLs
- Commissioning analytics: 6-month trend, aging buckets, top-failing scripts
- Deficiency board (kanban) with state machine and audit trail
- Lessons Learned knowledge base (7 categories)
- Closeout tracker per project
</details>

<details>
<summary><b>Operations</b></summary>

- Work orders full lifecycle (create, assign, status transitions, SLA)
- Preventive maintenance scheduler (daily cron)
- Inspection checklists (pass/fail, numeric, text)
- Asset hierarchy (parent/child components), health scoring
- Vendor management (contracts, scorecards, NTE pricing)
- Workflow automation rules (visual builder, 7 triggers, 5 actions)
</details>

<details>
<summary><b>Monitoring</b></summary>

- IoT sensor dashboard with 5-second polling, anomaly detection
- 7d × 24h anomaly density heatmap
- Interactive SVG floor plan with asset pins
- Asset health matrix (scatter plot with danger zone)
- Mapbox 3D facility map
</details>

<details>
<summary><b>Developer</b></summary>

- REST API v1 with Sanctum auth (20 endpoints)
- API documentation page (dark theme, syntax highlighting)
- Audit log viewer with old/new diff
- CMD+K command palette (fuzzy search across entities)
- 141 passing tests (485 assertions) covering full workflows and tenant isolation
</details>

---

## Run locally

```bash
git clone git@github.com:yashkuceriya/nexus-ops.git
cd nexus-ops
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --port=8001
```

Open http://localhost:8001, login `admin@acme.com` / `password`.

**Tests:** `php artisan test` → 141 passing.

---

## Deployment

Two documented paths in [`docs/DEPLOY.md`](docs/DEPLOY.md):

- **Railway** (recommended for demos) — one-click GitHub deploy, Postgres plugin, ~$5–15/mo, 15 min setup
- **AWS Fargate** — task defs + nginx + supervisord configs ready in `deploy/`; bring your own RDS + ALB + ECR

---

## Project structure

```
app/
├── Domain/                   Value objects + enums
├── Http/
│   ├── Controllers/Api/      REST API
│   └── Middleware/           Tenant guards (web + api)
├── Livewire/                 43 reactive components
├── Models/                   27 Eloquent models, all tenant-scoped
├── Services/
│   ├── TestExecution/        FPT engine
│   ├── Turnover/             Handover package builder
│   ├── Signoff/              Asset signoff workflow
│   ├── Checklist/            PFC runner
│   └── ...
├── Events/ Listeners/ Jobs/  Async pipeline
└── Console/Commands/         cx:weekly-digest, pm:generate
docs/
├── DESIGN.md                 Design system tokens + recipes
├── DEPLOY.md                 Railway + AWS playbooks
└── adr/                      Architecture decision records
```

---

## License

MIT.
