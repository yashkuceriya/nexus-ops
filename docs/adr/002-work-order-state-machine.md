# ADR-002: Work Order State Machine via PHP Constants (Enum-Ready)

## Status: Accepted
## Date: 2026-04-15

## Context

Work orders in NexusOps follow a defined lifecycle: `open -> assigned -> in_progress ->
on_hold -> completed -> verified`, with `cancelled` reachable from most states. We
needed to decide how to enforce valid status transitions and represent the set of
allowed statuses.

Three approaches were evaluated:
1. **Database-driven status config**: Store statuses and transitions in a `status_configs`
   table, editable per-tenant via admin UI.
2. **State machine library**: Use a package like `spatie/laravel-model-states` or
   `asantibanez/laravel-eloquent-state-machines` for transition enforcement.
3. **PHP constants with explicit transition map**: Define statuses and transitions as
   typed constants in the service layer, with transition validation in application code.

## Decision

We chose **PHP constants with an explicit transition map** defined in
`WorkOrderService`. The statuses are defined as `VALID_STATUSES` (a `list<string>`
constant) and transitions as `STATUS_TRANSITIONS` (a `map<string, list<string>>`
constant). The `updateStatus()` method validates transitions before persisting.

This approach is deliberately enum-ready: when the team chooses to introduce a
`WorkOrderStatus` backed enum, the constants convert directly to enum cases with
no structural change to the transition logic.

## Rationale

### Compile-Time Safety
PHP constants are resolved at compile time. A typo like `'in_progres'` will never
silently pass -- the `Rule::in()` validation in controllers catches it at the request
boundary, and the `STATUS_TRANSITIONS` map catches it at the service layer. With a
future PHP enum, this becomes even stronger: `WorkOrderStatus::InProgress` is checked
by the parser itself.

### Exhaustive Transition Map
The `STATUS_TRANSITIONS` constant is a complete adjacency list. Every status has an
explicit entry, including terminal states (`verified => []`). This makes it trivial
to audit the full state machine by reading one constant, unlike database-driven configs
where you need to query to understand the current transition rules.

### No Runtime DB Queries
Status validation requires zero database queries. The transition map lives in opcache
after the first request. For a system processing hundreds of status changes per hour,
this eliminates unnecessary round-trips that a database-driven approach would incur.

### IDE Support
Constants and future enums provide full autocompletion, find-usages, and refactoring
support in PhpStorm and VS Code. Database-driven statuses are opaque strings that
IDEs cannot analyze.

## Trade-offs

### Deployment Required to Change Transitions
Adding a new status or transition requires a code change and deployment. For this
domain, this is acceptable: facility management work order lifecycles are standardized
(based on CMMS industry patterns) and change at most once or twice per year. The
deployment requirement is actually a feature -- it forces transitions through code
review and testing rather than allowing ad-hoc admin changes that could break
integrations.

### No Per-Tenant Customization
All tenants share the same state machine. If a tenant needs a custom workflow (e.g.,
adding a `pending_approval` state), it requires a code change. We evaluated making
this configurable but decided against it: divergent workflows per tenant create
integration nightmares with external CMMS systems that expect standard status sets.

### Library vs. Hand-Rolled
We chose not to use `spatie/laravel-model-states` because:
- Our transition logic includes side effects (setting `started_at`, `completed_at`,
  `verified_at`, calculating `sla_breached`) that are tightly coupled to the domain.
- The service already encapsulates transitions in a single method with audit logging.
- Adding a library dependency for ~40 lines of transition logic was not justified.

## Future Evolution

When PHP enum adoption is complete across the codebase:
1. Create `App\Enums\WorkOrderStatus` as a `string` backed enum.
2. Replace `VALID_STATUSES` with `WorkOrderStatus::cases()`.
3. Move `STATUS_TRANSITIONS` to a static method on the enum: `WorkOrderStatus::allowedTransitions()`.
4. Update the `WorkOrder` model to cast `status` to the enum.
5. Controller validation uses `Rule::enum(WorkOrderStatus::class)`.
