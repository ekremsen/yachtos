# ADR-0011 - Authenticated Tenant Context Foundation

**Status:** Accepted

**Date:** 25 September 2026

**Decision authority:** CTO approval for this foundation increment

## Context

ADRs 0006-0010 establish module ownership, identity, memberships, authorization and request-scoped tenancy. They leave authentication mechanics and persistence details open. This increment implements authentication and current-tenant access only.

## Decision

- Store the password hash in `users.password`. `Support/Auth` owns authentication and credential operations; Users owns profile identity and lifecycle. Physical co-location does not assign authentication workflows to Users. No separate credentials table is introduced.
- Use Laravel Sanctum bearer tokens, hashed at rest and individually revocable. Default lifetime is 1,440 minutes, configurable through `SANCTUM_EXPIRATION_MINUTES`. No refresh tokens or cookie/session fallback.
- Derive tenant context exclusively from the authenticated user's valid active tenant membership. Missing or ambiguous memberships fail closed. Clients cannot select tenants through headers, query parameters or payloads.
- Bind `TenantContext` with request-scoped container lifetime and no static state. Resolve before tenant access and clear after requests, including exceptions.
- Revalidate user, membership and tenant status on each protected tenant request. Logout requires only a valid bearer token so membership loss cannot prevent revocation.
- Allow incomplete tenants in tests and internal foundation provisioning. Active access status does not certify a completed operational organization. Yacht, Owner/Captain, administrator and onboarding invariants remain deferred to their corresponding increments.
- Use UUID primary keys for users, tenants and memberships. Sanctum infrastructure token rows retain integer IDs and reference UUID users.

## Consequences

Existing accepted ADRs remain unchanged. This clarifies credential persistence under ADR-0007 and tenant resolution under ADR-0010 without superseding domain separation or completed-organization invariants.

Login requires an already-provisioned identity and membership. This increment exposes no registration, tenant/membership mutation, tenant switching, RBAC or operational onboarding. Future business endpoints still require their own policies and scoped queries.

Production remains PostgreSQL-oriented. SQLite is supported locally and for isolated tests; PostgreSQL verification remains a separate release check.

## Alternatives not selected

- Separate credentials table: unnecessary for the approved increment.
- Cookie authentication: outside the approved bearer contract.
- Client-selected tenant: unnecessary for the single-organization MVP.
- Refresh tokens and broad authorization infrastructure: deferred to keep this increment bounded.

See [Authenticated Tenant Context](../architecture/authenticated-tenant-context.md) for the implementation contract.
