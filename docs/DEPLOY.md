# Deployment

Two supported paths: **Railway** (fast, cheap, recommended) and **AWS Fargate** (existing configs in `deploy/`).

## Prerequisites (both paths)
- Postgres 14+ or MySQL 8+ (SQLite is dev-only)
- `APP_KEY` generated: `php artisan key:generate --show`
- Sparkline/heatmap SQL was rewritten to be driver-agnostic — no migration required

---

## Path A — Railway (recommended)

**Cost:** ~$5–15/month. **Setup time:** 15 min.

### One-time setup
1. Go to `railway.app` → New Project → Deploy from GitHub → select `yashkuceriya/nexus-ops`.
2. Add a **Postgres** plugin in the same project (Railway auto-injects `DATABASE_URL`, `PGHOST`, etc.).
3. In your app service → **Variables**, set:
   ```
   APP_NAME=NexusOps
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://<your-service>.up.railway.app
   APP_KEY=base64:...          # from `php artisan key:generate --show`
   DB_CONNECTION=pgsql
   DB_HOST=${{Postgres.PGHOST}}
   DB_PORT=${{Postgres.PGPORT}}
   DB_DATABASE=${{Postgres.PGDATABASE}}
   DB_USERNAME=${{Postgres.PGUSER}}
   DB_PASSWORD=${{Postgres.PGPASSWORD}}
   SESSION_DRIVER=database
   CACHE_STORE=database
   QUEUE_CONNECTION=database
   TRUSTED_PROXIES=*
   ```
4. Railway picks up `railway.json` for build + start. First deploy runs `php artisan migrate --force` automatically.

### Add worker + scheduler services (same project, same repo)
- **Worker** → New service → Same repo → custom start command:
  ```
  php artisan queue:work --sleep=3 --tries=3 --max-time=3600
  ```
- **Scheduler** → New service → Same repo → custom start command:
  ```
  while true; do php artisan schedule:run; sleep 60; done
  ```

### After first deploy
- Open Railway console → run `php artisan db:seed` once for demo data (skip in real tenants).
- Attach a custom domain in service settings; Railway handles TLS.

---

## Path B — AWS Fargate (existing configs)

**Cost:** ~$80–120/month baseline. **Setup time:** 4–8 hrs if you've never done it.

What's already in `deploy/`:
- `taskdef-web.json` — ECS task definition for web (FPM + Nginx in one task)
- `taskdef-worker.json` — ECS task definition for queue worker
- `nginx.conf` — reverse-proxy config for the web task
- `supervisord.conf` — process supervisor for FPM + Nginx
- GitHub Actions workflow (if present in `.github/workflows/`) builds + pushes to ECR via OIDC

### What's missing / still to do
1. **ECR repo** — `aws ecr create-repository --repository-name nexus-ops`
2. **RDS Postgres** (or MySQL) — single db.t4g.micro is fine to start; note the endpoint.
3. **ElastiCache Redis** (optional but recommended for sessions + cache + queue).
4. **Secrets in AWS Secrets Manager** — `APP_KEY`, `DB_PASSWORD`, `MAPBOX_TOKEN`, etc. Reference them in the task defs via `secrets: [{ name, valueFrom }]`.
5. **ALB + ACM cert** — target the web task group on port 80.
6. **VPC with public + private subnets** — private for RDS/Redis/Fargate; public for ALB + NAT.
7. **EventBridge schedule** running `php artisan schedule:run` every minute against a one-shot Fargate task (cheaper than a persistent scheduler container).
8. **IAM roles** — task execution role (pull from ECR, read Secrets Manager) + task role (app permissions).

Fill `.env.production.example` (already in repo root) with real values and load them via Secrets Manager.

### Quick version
Run the stack through **Laravel Vapor** if you want AWS but not the plumbing — but note Vapor is Lambda-based and Livewire's WebSocket ambitions don't fit that model.

---

## Release checklist (both paths)
- [ ] `APP_DEBUG=false`, unique `APP_KEY`
- [ ] Postgres/MySQL attached; migrations run
- [ ] Queue worker running (notifications, digest job)
- [ ] Scheduler running (`pm:generate` daily, `cx:weekly-digest` Mondays)
- [ ] Admin password rotated (`admin@acme.com / password` is demo data)
- [ ] Custom domain + TLS
- [ ] First login succeeds, dashboard renders
- [ ] PDF download works (turnover package, FPT report)
