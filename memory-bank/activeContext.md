# Active Context — NexusOps

## Current State
All major features are implemented and working. The app has 22 pages, 84 PHP files, 30 Livewire components, and 29 passing tests.

## Recently Completed (this session)
1. Full project scaffold with 21 models, 26 migrations, comprehensive seeder
2. API connector with retry/backoff/circuit breaker pattern
3. Work order engine with status machine, SLA tracking, multi-source creation
4. IoT sensor dashboard with real-time Chart.js visualization
5. Professional Stitch-inspired UI (dark sidebar, emerald accent)
6. Work order detail page with status timeline + create/edit modal
7. Asset detail page with QR code, tabbed view
8. Notification system (in-app bell + email)
9. Authorization policies (role-based)
10. PM scheduler (`pm:generate` artisan command, daily cron)
11. Inspection checklists (pass/fail, numeric, text steps)
12. Occupant request portal (public, token-based tracking)
13. Analytics & reports page (6 Chart.js charts)
14. Audit log viewer with diff view
15. Interactive SVG floor plan
16. Asset health scoring (weighted algorithm, scatter plot matrix)
17. Workflow automation rules engine (visual builder)
18. Vendor management (contracts, scorecards, NTE pricing)
19. Real-time Livewire polling + event broadcasting
20. SaaS landing page (animated gradient hero, counter stats)
21. CMD+K command palette (fuzzy search)
22. Dark mode toggle
23. Mapbox 3D facility map (pulsing markers, fly-to)
24. AI insights panel (simulated, typewriter animation)
25. API documentation page (dark code blocks, syntax highlighting)
26. Skeleton loaders + micro-interactions
27. Docker + AWS Fargate deployment pipeline
28. GitHub Actions CI/CD (test + deploy workflows)

## What Works
- All 22 pages return HTTP 200
- 29 tests pass (56 assertions)
- Zero dead links in the app (16 on landing page are marketing placeholders)
- Zero forbidden references (no "Facility Grid" or "FG" in user-visible text)
- Seeder creates realistic demo data
- PM scheduler command works

## Known Limitations
- Internal code still has `facilitygrid_*` DB column names (these are field identifiers, not user-visible)
- The connector service uses placeholder API endpoints (real API docs not publicly available)
- Some services in `app/Services/FacilityGrid/` have "FacilityGrid" in the class name (internal, not user-visible)
- SQLite used for dev — some features may need adjustment for MySQL in production
- Mapbox uses a public demo token that may have rate limits
- No actual ML/AI — the "AI Insights" panel generates insights from rule-based analysis of real data

## Next Phase: What to Build
- Git init + proper commit history (project has NO version control)
- More Livewire component tests + edge case coverage
- Dashboard KPI caching (tagged cache with invalidation)
- PDF report export from /reports page
- Fix any remaining N+1 queries (preventLazyLoading is on)
- Verify dark mode works across all 22 pages
- Clean up console errors in browser
- Consider live deployment (Railway/Render for free hosting)
