# NexusOps

> Intelligent Facility Operations Platform

NexusOps bridges building commissioning and closeout data into day-one facility operations. It provides a unified dashboard for readiness tracking, work order management, IoT sensor monitoring, asset health scoring, vendor oversight, and workflow automation — designed to feel like a premium SaaS product rather than a typical CRUD application.

## Features

**Operations**
- Portfolio dashboard with KPI counters, readiness rings, sparklines, and activity feed
- Work order full lifecycle (create, assign, status transitions, SLA tracking)
- Inspection checklists with pass/fail, numeric, and text steps
- Preventive maintenance scheduler with automatic work order generation

**Monitoring**
- Real-time IoT sensor dashboard with 5-second polling and anomaly detection
- Interactive SVG floor plan with asset pins
- Asset health matrix (scatter plot with danger zone highlighting)
- Mapbox 3D facility map with dark theme, pulsing markers, and fly-to navigation

**Management**
- Vendor management with contracts, scorecards, and NTE pricing
- Analytics and reports with six chart types and date filtering
- Visual workflow automation builder (7 triggers, 5 actions)

**Public Portal**
- Occupant request submission (no authentication required)
- Token-based request status tracking with satisfaction survey
- SaaS landing page with animated hero section

**Developer**
- REST API with 17 endpoints and Sanctum token authentication
- Interactive API documentation page with syntax highlighting
- Audit log viewer with old/new value diff display

**Technical Polish**
- CMD+K command palette with fuzzy search
- Dark mode toggle with localStorage persistence
- Skeleton loaders, toast notifications, and micro-interactions
- Livewire polling at 5s/10s/15s intervals for real-time updates

## Tech Stack

| Layer | Technology |
|-----------|--------------------------------------------------|
| Backend | Laravel 13, PHP 8.5, Livewire 4.2 |
| Frontend | Tailwind CSS v4, Alpine.js v3, Chart.js v4, Mapbox GL JS v3 |
| Database | SQLite (dev), Aurora MySQL (prod) |
| Queue | Redis / Amazon SQS |
| Auth | Laravel Sanctum (API tokens + session) |
| Multi-tenant | spatie/laravel-multitenancy |
| Testing | Pest v4 |
| Deploy | Docker (PHP 8.3-FPM + Nginx), AWS ECS Fargate (ARM64 Graviton) |
| CI/CD | GitHub Actions with OIDC |

## Quick Start

```bash
# Clone the repository
git clone <repo-url> nexus-ops && cd nexus-ops

# Install dependencies
composer install
npm install && npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Set up database and seed demo data
php artisan migrate --seed

# Start the development server
php artisan serve --port=8001
```

Open [http://localhost:8001](http://localhost:8001) in your browser.

## Demo Credentials

| Email | Password | Role |
|--------------------|----------|------------|
| admin@acme.com | password | Admin |
| manager@acme.com | password | Manager |
| tech1@acme.com | password | Technician |

## Architecture

NexusOps is a multi-tenant Laravel application using Livewire for reactive server-rendered UI. All data is scoped by `tenant_id` with middleware enforcement. The event-driven backend uses observers and listeners for audit logging, SLA breach detection, and sensor anomaly alerts. A REST API layer provides external integration points secured by Sanctum tokens.

### Directory Structure

```
app/
  Http/Controllers/Api/   # 8 API controllers
  Livewire/               # 30 reactive components
  Models/                 # 21 Eloquent models
  Services/               # 9 service classes
  Events/                 # 5 domain events
  Notifications/          # 3 notification types
  Policies/               # 3 authorization policies
routes/
  web.php                 # 59 routes (22 pages)
  api.php                 # 17 API endpoints
database/
  migrations/             # 26 migration files
  seeders/                # Demo data seeder
resources/views/          # 38 Blade templates
```

### Models (21)

Tenant, User, Project, Location, Asset, WorkOrder, Issue, SensorSource, SensorReading, CloseoutRequirement, Document, AuditLog, StatusMapping, SyncWatermark, MaintenanceSchedule, ChecklistTemplate, ChecklistCompletion, OccupantRequest, AutomationRule, Vendor, VendorContract

## Pages (22)

| Route | Page | Auth |
|-------------------------------|----------------------------------------|------|
| `/` | Landing page | No |
| `/login` | Login | No |
| `/request` | Occupant request form | No |
| `/request/{token}` | Request status tracker | No |
| `/dashboard` | Portfolio dashboard with KPIs | Yes |
| `/projects` | Project list | Yes |
| `/projects/{id}` | Project detail with readiness scoring | Yes |
| `/work-orders` | Work order list with filters | Yes |
| `/work-orders/{id}` | Work order detail and status management | Yes |
| `/assets` | Asset registry | Yes |
| `/assets/{id}` | Asset detail with QR code and history | Yes |
| `/sensors` | IoT sensor dashboard | Yes |
| `/floor-plan` | Interactive SVG floor plan | Yes |
| `/health-matrix` | Asset health scatter plot | Yes |
| `/map` | Mapbox 3D facility map | Yes |
| `/vendors` | Vendor list | Yes |
| `/vendors/{id}` | Vendor detail with contracts | Yes |
| `/reports` | Analytics and reports | Yes |
| `/automation` | Automation rule list | Yes |
| `/automation/create` | Visual rule builder | Yes |
| `/automation/{id}/edit` | Edit automation rule | Yes |
| `/audit-log` | Audit log viewer | Yes |
| `/docs` | API documentation | Yes |

## API Endpoints

All endpoints are prefixed with `/api` and return JSON. Authenticated routes require a Sanctum bearer token.

| Method | Path | Description |
|--------|----------------------------------------------|----------------------------------------------|
| POST | `/api/auth/login` | Authenticate and receive API token |
| GET | `/api/auth/me` | Get current user profile |
| POST | `/api/auth/logout` | Revoke current token |
| GET | `/api/dashboard` | Dashboard summary data |
| GET | `/api/dashboard/kpis` | Key performance indicators |
| GET | `/api/dashboard/sensors` | Sensor overview stats |
| GET | `/api/dashboard/projects/{id}/readiness` | Project readiness score |
| GET | `/api/work-orders` | List work orders (filtered, paginated) |
| POST | `/api/work-orders` | Create a work order |
| GET | `/api/work-orders/{id}` | Get work order detail |
| PUT | `/api/work-orders/{id}` | Update a work order |
| PATCH | `/api/work-orders/{id}/status` | Transition work order status |
| GET | `/api/assets` | List assets |
| GET | `/api/assets/{id}` | Get asset detail |
| GET | `/api/assets/qr/{code}` | Look up asset by QR code |
| GET | `/api/sensors` | List sensor sources |
| POST | `/api/sensors/ingest` | Ingest sensor readings |
| GET | `/api/sensors/{id}/readings` | Get readings for a sensor |
| POST | `/api/sync/trigger` | Trigger data sync |
| GET | `/api/sync/status` | Get sync watermark status |

## Deployment

### Docker

```bash
docker-compose up -d
```

The compose file starts five services: app (PHP-FPM + Nginx), queue worker, scheduler, MySQL, and Redis.

### AWS Fargate

The project includes ECS task definitions for web and worker containers targeting ARM64 Graviton instances. GitHub Actions handles CI/CD with OIDC authentication to push images to ECR and deploy to ECS.

## Environment Variables

See `.env.production.example` for the full list. Key categories:

- **App** -- `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`
- **Database** -- `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- **Queue** -- `QUEUE_CONNECTION`, `REDIS_HOST`, `SQS_QUEUE`
- **Services** -- `MAPBOX_TOKEN`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`
- **Auth** -- `SANCTUM_STATEFUL_DOMAINS`

## Testing

```bash
php artisan test
```

29 tests with 56 assertions covering API endpoints, services, and model relationships. Tests use Pest v4 with SQLite in-memory database.

## License

MIT
