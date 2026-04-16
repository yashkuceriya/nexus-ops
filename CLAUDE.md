# NexusOps — AI Assistant Context

## CRITICAL RULES
- **NEVER mention "Facility Grid", "FG Bridge", or "FG"** in any user-visible text, comments, file names, or folder names
- The app is branded **"NexusOps"** exclusively
- Internal DB columns like `facilitygrid_*` are backend identifiers — don't rename them but don't expose them in UI

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
