# NexusOps — AI Assistant Context

## CRITICAL RULES
- **ZERO occurrences anywhere** of "Facility Grid", "FacilityGrid", "facilitygrid", "FG Bridge", "fg_bridge", or the acronym "FG" (including "FG-" prefixes) — applies to UI text, comments, file names, folder names, class names, namespaces, DB columns, seed values, and all internal code
- The app is branded **"NexusOps"** exclusively
- External integration code lives under `app/Services/ExternalSync/` with neutral naming (e.g., `ExternalSyncClient`, `external_*` DB columns)

## Quick Reference
- **Framework**: Laravel 13, Livewire 4.2, PHP 8.5
- **Frontend**: Tailwind CSS (CDN), Alpine.js (CDN), Chart.js (CDN), Mapbox GL JS (CDN)
- **DB**: SQLite (dev) — avoid FIELD(), DATE_FORMAT() — use CASE WHEN, strftime()
- **Auth**: Sanctum (API), session (web), roles: owner/admin/manager/technician/readonly
- **QR Code**: `SimpleSoftwareIO\QrCode\Facades\QrCode` (not SimpleQrCode)

## Dev Commands
```bash
php artisan serve --port=8001
php artisan migrate:fresh --seed
php artisan test
php artisan pm:generate
```

## Login
admin@acme.com / password

## Memory Bank
Full project context is in `memory-bank/` directory:
- `projectbrief.md` — goals and constraints
- `productContext.md` — what it does, who uses it
- `techContext.md` — stack, libraries, file counts
- `systemPatterns.md` — architecture, design patterns
- `activeContext.md` — current state, recent work
- `progress.md` — feature checklist, stats
