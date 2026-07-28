# ADR-0010 - Tenant Context Strategy

**Status:** Accepted

**Date:** 02 July 2026

---

## Context

YachtOS is a multi-tenant commercial SaaS platform for yacht operations.

Every business record in the platform belongs to exactly one tenant. Cross-tenant data access is strictly prohibited.

Previous architectural decisions established:

- **ADR-0007:** User identity is separate from operational and access concerns.
- **ADR-0008:** Users are connected to organizations through membership relationships.
- **ADR-0009:** Authorization determines permitted actions within business context.

The platform must therefore determine which tenant a request operates within before business logic executes.

Without an explicit tenant context strategy, tenant filtering may be applied inconsistently across modules. That creates isolation risk, duplicated logic and difficult-to-audit access behavior.

This ADR defines how tenant context is resolved inside YachtOS.

It does not define authentication implementation, membership implementation or authorization implementation. Those concerns are addressed in separate architectural decisions.

---

## Decision

YachtOS adopts a **request-scoped tenant context model**.

Every protected business request must execute inside exactly one active tenant context.

Tenant context is resolved once per request and applied consistently across all business operations within that request.

### Active Tenant

The **Active Tenant** is the tenant organization within which the current request is authorized to operate.

At any point during a protected request, the application must be able to answer:

**"Which tenant is this request operating within?"**

The Active Tenant defines the boundary for:

- Business data access
- Module operations
- Resource visibility
- Tenant-scoped validation

A request without a valid Active Tenant must not execute tenant-scoped business logic.

There must never be more than one Active Tenant per request.

### Tenant Resolution

**Tenant Resolution** is the process of determining the Active Tenant for a request.

Tenant resolution occurs after identity has been established for the request and before tenant-scoped business logic executes.

Tenant resolution must produce a single, explicit Active Tenant for the request.

If tenant resolution fails, the request must be rejected.

Tenant resolution must not rely on implicit assumptions within individual modules.

Each module must consume the resolved tenant context rather than determining tenant scope independently.

The specific source used to resolve the Active Tenant is an implementation concern outside this ADR.

This ADR requires only that resolution is explicit, centralized and consistent.

### Request Context

**Request Context** is the set of request-scoped values available to the application during a single HTTP request.

Tenant context is part of Request Context.

Once resolved, the Active Tenant must remain available for the full lifetime of the request.

Business modules, services and data access layers must read tenant scope from Request Context rather than accepting tenant identifiers ad hoc from untrusted input.

Request Context ensures that:

- Tenant scope is resolved once
- Tenant scope is applied consistently
- Business logic does not re-derive tenant scope differently in different layers

Tenant context in Request Context is mandatory for all tenant-scoped business operations.

### Tenant Isolation

**Tenant Isolation** means that a request operating within one Active Tenant may access only data belonging to that tenant.

Tenant isolation is a core platform rule.

Every tenant-scoped business operation must assume that records outside the Active Tenant do not exist for that request.

Cross-tenant access is prohibited.

Tenant isolation must be enforced by architecture, not by optional developer discipline in individual modules.

Request Context provides the tenant boundary used to enforce isolation consistently across the platform.

A valid business permission alone does not override tenant isolation.

Operations must both resolve the correct Active Tenant and remain within that tenant boundary.

### Middleware Responsibility

**Middleware** is responsible for establishing and enforcing request-level tenant context.

Middleware must:

- Run before tenant-scoped business logic executes
- Resolve or validate the Active Tenant for the request
- Reject requests that cannot establish a valid tenant context
- Make the Active Tenant available through Request Context

Middleware owns the request boundary for tenant context.

Business controllers and services must not re-implement tenant resolution at the entry point of each request.

Centralizing tenant context in middleware ensures consistent behavior across all modules and reduces the risk of accidental cross-tenant access.

Middleware enforces the presence of tenant context.

Modules consume tenant context.

Modules must not own request-level tenant resolution.

### Why Every Request Must Execute Inside a Tenant Context

Every protected business request in YachtOS must execute inside a tenant context because:

- Every business record belongs to exactly one tenant.
- Cross-tenant access must be prohibited by architecture.
- Business modules depend on a shared tenant boundary to operate safely.
- Data access, validation and resource ownership all require a known tenant scope.
- A commercial SaaS platform must guarantee tenant isolation consistently, not selectively.
- Central tenant context prevents duplicated and inconsistent tenant filtering across modules.
- Auditing, debugging and security review require a clear tenant scope per request.

A request without tenant context is ambiguous and unsafe for tenant-scoped business operations.

YachtOS therefore treats tenant context as mandatory infrastructure for protected business requests.

---

## Consequences

### Positive

- Tenant scope is explicit and consistent for every protected request.
- Cross-tenant isolation becomes easier to enforce platform-wide.
- Business modules consume a shared tenant boundary instead of resolving scope independently.
- Middleware provides a single request entry point for tenant context enforcement.
- Debugging and auditing become clearer because each request has one Active Tenant.
- The strategy aligns with ADR-0008 and ADR-0009 without mixing membership or authorization implementation details.

### Negative

- All protected business requests require successful tenant context resolution.
- Middleware becomes a critical part of the request pipeline.
- Requests that cannot resolve tenant context must fail rather than proceed ambiguously.
- Modules must depend on shared request infrastructure instead of managing tenant scope locally.

---

## Alternatives Considered

### Per-Module Tenant Resolution

Allow each module to determine tenant scope independently inside controllers or services.

Rejected because:

- Tenant resolution would become inconsistent across modules.
- Cross-tenant isolation would depend on module-level discipline.
- Duplicated logic would increase maintenance cost and security risk.
- Request behavior would be harder to audit.

### Optional Tenant Context

Allow some business requests to execute without an Active Tenant.

Rejected because:

- Tenant-scoped business data would become ambiguous.
- Cross-tenant access risk would increase.
- Business modules would need conditional tenant logic throughout the codebase.
- SaaS tenant isolation would no longer be guaranteed by architecture.

### Tenant Context Passed Manually on Every Call

Require each controller, service and query to accept a tenant identifier parameter explicitly.

Rejected because:

- Tenant scope would be easy to omit or pass incorrectly.
- Request-level consistency would be difficult to maintain.
- Middleware and Request Context provide a safer and more centralized model.

### Database-Only Tenant Isolation

Rely on developers to remember tenant filtering in queries without request-level tenant context.

Rejected because:

- Isolation would depend on manual filtering rather than platform architecture.
- Missing tenant filters would create cross-tenant data exposure risk.
- Tenant scope would not be explicit at the request boundary.

---

## Final Decision

YachtOS will use a **request-scoped tenant context model**.

Every protected business request must execute inside exactly one **Active Tenant**.

**Tenant Resolution** must determine the Active Tenant explicitly and centrally before tenant-scoped business logic runs.

The resolved Active Tenant must be available through **Request Context** for the lifetime of the request.

**Tenant Isolation** must ensure that business operations access only data belonging to the Active Tenant.

**Middleware** is responsible for establishing and enforcing tenant context at the request boundary.

Business modules must consume tenant context and must not own request-level tenant resolution.

This tenant context strategy is required for secure and consistent multi-tenant operation in YachtOS.

Authentication, membership and authorization implementation details will be defined in separate architectural decisions.
