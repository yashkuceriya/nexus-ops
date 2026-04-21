# LinkedIn post drafts

Three variants — short to long. Pick one, adjust the opening line to sound like you, paste it with 2–3 screenshots.

---

## Variant 1 — Short (hook + link)

Spent the last few weeks building **NexusOps** — a multi-tenant facility commissioning and operations platform for data centers, hospitals, and research labs.

It takes a construction project through the final 20% (Functional Performance Tests, Pre-Functional Checklists, deficiency resolution, turnover packages) and hands it off to day-one ops with work orders, IoT telemetry, and a living audit trail.

Built with Laravel 13, Livewire 4.2, Tailwind, Alpine, Postgres. 138 passing tests. Multi-tenant by default.

Live demo (admin@acme.com / password): https://REPLACE_WITH_RAILWAY_URL
Code: https://github.com/yashkuceriya/nexus-ops

#Laravel #Livewire #PHP #SaaS #SideProject

---

## Variant 2 — Medium (the "what I learned" post)

I shipped a portfolio project I'm genuinely proud of: **NexusOps**, a facility commissioning and operations platform.

→ Live: https://REPLACE_WITH_RAILWAY_URL (admin@acme.com / password)
→ Code: https://github.com/yashkuceriya/nexus-ops

**What's inside:**
• FPT engine with witness signatures, tamper detection, auto-eval (tolerance/GTE/between), parent-child retest chains
• Pre-Functional Checklist runner that auto-opens deficiency tickets on failure
• Turnover package builder → signed PDF → stakeholder share URL
• Real-time IoT sensor dashboard with anomaly heatmap (7d × 24h)
• Kanban deficiency board with state machine + audit log
• Portfolio dashboard with SVG sparklines, Chart.js velocity chart, donut, readiness heatmap

**Technical highlights:**
• Multi-tenancy via a global scope trait + web/API middleware — cross-tenant isolation covered by integration tests
• Domain-driven: value objects, PHP 8 enums, a real work-order state machine
• 43 Livewire components, CMD+K palette, no build step (Tailwind CDN, Alpine CDN)
• Sanctum API (17 endpoints, 24h tokens), role-based policies, audit log with diff
• 138 passing tests, 470 assertions, driver-agnostic SQL (SQLite dev → Postgres prod)

**What I learned:**
Domain modeling pays off. The moment I introduced `WorkOrderStatus` enum + `SlaPolicy` value object, tests got tighter, bugs got rarer, and every feature after that was cheaper.

Built with Laravel 13 + Livewire 4.2. Feedback welcome — especially from folks who've built commissioning or facilities software.

#Laravel #Livewire #PHP #SoftwareEngineering #DomainDrivenDesign

---

## Variant 3 — Long (recruiter-optimized)

I just open-sourced **NexusOps**, a production-shape multi-tenant SaaS I built to demonstrate senior Laravel engineering.

🔗 Live demo: https://REPLACE_WITH_RAILWAY_URL
🔗 Source: https://github.com/yashkuceriya/nexus-ops
Login: admin@acme.com / password

**The problem it solves**
Building commissioning — the final 20% of a construction project where every system is verified against design intent — still runs on spreadsheets and PDFs in most of the industry. NexusOps gives commissioning agents a real workflow: test scripts, signed executions, deficiency tracking, stakeholder turnover, handoff to operations.

**What's built**
→ Functional Performance Testing engine (auto-eval, witness signatures, retest chains)
→ Pre-Functional Checklists with multi-session resume
→ Turnover package builder → PDF with QR codes → signed public share URL
→ Deficiency kanban board with state machine + audit trail
→ Work order engine with SLA tracking, escalation, lifecycle state machine
→ IoT sensor dashboard with 5s polling + 7d×24h anomaly heatmap
→ Portfolio dashboard (sparklines, velocity chart, donut, readiness heatmap)
→ Asset signoff workflow (4 states, signature hashing)
→ Lessons Learned knowledge base
→ Weekly CX digest notification via scheduled command

**Engineering decisions I'm happy with**
✓ Multi-tenancy via `BelongsToTenant` global scope trait, two separate middlewares for web vs JSON APIs, cross-tenant isolation covered by tests
✓ Domain layer: `WorkOrderStatus` + `Priority` enums, `SlaPolicy` + `ReadinessScore` value objects — no string comparisons
✓ Driver-agnostic SQL so the same code runs on SQLite (dev), MySQL, and Postgres
✓ Reactive UI without a SPA: 43 Livewire components, Alpine for interactions, inline SVG for viz
✓ Event-driven async: `TestExecutionCompleted` → notification listener, queue worker, scheduler
✓ PDF generation for turnover packages and FPT reports with signature-aware layouts
✓ REST API v1 under Sanctum (24h token expiry), role-based policies
✓ 138 passing tests (470 assertions): lifecycle, tenant isolation, FPT engine, retest chains, tamper detection

**Stack**
Laravel 13 · Livewire 4.2 · PHP 8.5 · Tailwind · Alpine · Chart.js · Postgres · Docker · Railway (deployed) · AWS Fargate configs (in repo)

**Looking for**
Senior/staff roles working on Laravel, Livewire, or SaaS platforms where multi-tenancy, domain modeling, and UX aren't afterthoughts. Open to remote or [your city].

#Laravel #Livewire #PHP #SaaS #OpenToWork #SoftwareEngineering #MultiTenant #DomainDrivenDesign
