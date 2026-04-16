# Tech Context — NexusOps

## Stack
- **Backend**: Laravel 13 (PHP 8.5), Livewire 4.2
- **Frontend**: Tailwind CSS v4 (CDN), Alpine.js v3 (CDN), Chart.js v4 (CDN), Mapbox GL JS v3 (CDN)
- **Database**: SQLite (dev), Aurora MySQL (prod)
- **Queue**: Redis/SQS, Laravel Sanctum for API auth
- **Deploy**: Docker (multi-stage, PHP 8.3-FPM + Nginx + Supervisor), AWS ECS Fargate (ARM64 Graviton)
- **CI/CD**: GitHub Actions with OIDC → ECR → ECS

## Key Libraries
- `livewire/livewire` ^4.2 — reactive UI components
- `laravel/sanctum` ^4.3 — API token auth
- `spatie/laravel-multitenancy` ^4.1 — multi-tenant support
- `simplesoftwareio/simple-qrcode` ^4.2 — QR code generation (namespace: `SimpleSoftwareIO\QrCode\Facades\QrCode`)
- `aws/aws-sdk-php` ^3.379 — S3, SQS
- `predis/predis` ^3.4 — Redis client
- `pestphp/pest` ^4.6 — testing

## File Counts (as of last build)
- 84 PHP files in app/
- 30 Livewire components
- 38 Blade views
- 21 Eloquent models
- 9 service classes
- 26 migrations
- 59 routes
- 29 tests (56 assertions)

## Dev Commands
```bash
php artisan serve --port=8001    # Start dev server
php artisan migrate:fresh --seed # Reset DB with demo data
php artisan test                 # Run Pest tests
php artisan pm:generate          # Run PM scheduler manually
```

## Login Credentials (seeded)
- admin@acme.com / password (Admin)
- manager@acme.com / password (Manager)
- tech1@acme.com / password (Technician)

## Known SQLite Gotchas
- No FIELD() function — use CASE WHEN for custom ordering
- No DATE_FORMAT() — use strftime('%Y-%m', column)
- Enums are CHECK constraints — inserting invalid values throws constraint violation
