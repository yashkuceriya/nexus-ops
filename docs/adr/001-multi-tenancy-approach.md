# ADR-001: Multi-Tenancy via Shared Schema with Global Scope

## Status: Accepted
## Date: 2026-04-15

## Context

NexusOps serves multiple facility management organizations (tenants) from a single
deployed codebase. Each tenant manages their own projects, assets, work orders, sensors,
and vendor relationships. We needed to decide how to isolate tenant data while keeping
the system operationally simple for an MVP targeting 10-200 concurrent tenants.

Three approaches were evaluated:
1. **Database-per-tenant**: Each tenant gets a dedicated database instance.
2. **Schema-per-tenant**: Shared database server, separate schemas per tenant.
3. **Shared schema with tenant_id discrimination**: Single database, all tables include
   a `tenant_id` foreign key, data isolation enforced at the application layer.

## Decision

We chose **shared schema with `tenant_id` column + a `BelongsToTenant` trait** that
applies a global Eloquent scope. Every tenant-scoped model uses this trait, which:

- Automatically adds `WHERE tenant_id = ?` to all queries via `addGlobalScope()`.
- Automatically sets `tenant_id` on the `creating` model event from the authenticated
  user's `tenant_id`, preventing accidental cross-tenant writes.
- Provides a `scopeForTenant($tenantId)` local scope for service-layer queries that
  operate outside the request lifecycle (queue jobs, CLI commands).

API controllers additionally pass `$request->user()->tenant_id` explicitly to service
methods as a defense-in-depth measure, visible in `WorkOrderController` and
`SensorController`.

## Rationale

- **Operational simplicity**: One migration path, one backup strategy, one connection
  pool. At 10-200 tenants this is well within PostgreSQL/MySQL shared-table capacity.
- **Global scope prevents data leaks**: Developers cannot accidentally forget a WHERE
  clause -- the scope is always applied unless explicitly removed with `withoutGlobalScope`.
- **Cross-tenant analytics**: Admin dashboards and platform-level reporting can query
  across tenants by removing the scope in a controlled admin context.
- **Creating event auto-sets tenant_id**: Eliminates an entire class of bugs where
  records are created without tenant context.

## Consequences

### Positive
- Single migration path -- no per-tenant DDL orchestration.
- Simpler backup and restore -- one database to snapshot.
- Cross-tenant analytics queries are straightforward.
- Connection pooling is efficient (single database).

### Negative
- **Noisy neighbor risk**: A tenant with large data volumes affects query performance
  for all tenants. Mitigated with composite indexes on `(tenant_id, ...)`.
- **Raw query risk**: Any `DB::select()` or raw SQL bypasses global scopes. Code review
  must enforce that raw queries always include tenant_id predicates.
- **Regulatory isolation**: Some enterprise customers may require physical data
  separation for compliance (SOC 2 Type II, GDPR). Shared schema does not satisfy this.

## Migration Path

When enterprise customers require physical isolation:

1. Introduce a `TenantConnection` resolver that maps `tenant_id` to a database
   connection name in `config/database.php`.
2. The `BelongsToTenant` trait already scopes by tenant_id -- extend it to also call
   `$this->setConnection()` based on the resolved connection.
3. Run a data migration script that exports a tenant's rows into a dedicated schema
   and updates the connection mapping.
4. Shared-schema tenants continue to work unchanged; isolated tenants route to their
   own database. This hybrid model allows incremental migration without a flag day.
