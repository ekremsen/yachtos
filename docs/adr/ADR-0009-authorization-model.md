# ADR-0009 - Authorization Model

**Status:** Accepted

**Date:** 02 July 2026

---

## Context

YachtOS is a multi-tenant commercial SaaS platform for yacht operations.

ADR-0007 established that a **User** is an identity and profile record only.

ADR-0008 established that users are connected to organizations and yachts through separate membership concepts.

The platform must also determine what an authenticated user is allowed to do within a tenant and, when applicable, within yacht-scoped operational context.

Authorization must therefore be defined as a separate architectural concern from user identity.

Without an explicit authorization model, permission logic risks being embedded in user records, controllers or individual modules. That leads to inconsistent access control, difficult auditing and poor long-term maintainability.

This ADR defines the authorization model only.

It does not define authentication, membership models, database structures or implementation details. Those concerns are addressed in separate architectural decisions.

---

## Decision

YachtOS adopts **Role-Based Access Control (RBAC)** as its authorization model.

Authorization determines what an authenticated user is allowed to do.

Authorization answers:

**"What are you allowed to do?"**

### RBAC

YachtOS uses RBAC as the primary authorization strategy.

Under RBAC:

- Permissions represent allowed actions.
- Roles group permissions.
- Users receive permissions through assigned roles.

RBAC provides a consistent and scalable way to manage access across a growing SaaS platform with multiple modules, tenants and operational workflows.

Authorization decisions must always consider business context, not permission alone.

A valid permission is insufficient if the requested resource belongs to another tenant or falls outside the user's authorized scope.

### Roles

A **Role** represents a named set of responsibilities within a tenant.

Roles simplify administration by grouping related permissions.

Examples include:

- Administrator
- Owner
- Captain
- Engineer
- Crew

Roles belong to the authorization model.

Roles must not be stored as direct attributes of the User identity.

A user receives effective access through role assignment within the authorization domain, not through profile fields in the Users module.

Roles may evolve over time as the product grows, including future support for custom tenant-defined roles.

### Permissions

A **Permission** represents a specific allowed action on a resource or business domain.

Permissions must be action-based and independent of user interface structure.

Examples include:

- users.view
- users.create
- yachts.update
- maintenance.complete
- expenses.approve

Permissions should describe what action may be performed, not how a screen or button is labeled.

Permissions are assigned to roles.

Users receive permissions through their assigned roles.

Permission naming must remain stable even when UI or API presentation changes.

### Policies

Business authorization must be enforced through **policies**.

Policies centralize authorization logic and keep application entry points free from permission rules.

A policy-based approach ensures that authorization decisions are:

- Consistent across modules
- Explicit and reviewable
- Separate from user profile management
- Easier to test and audit

Policies evaluate whether a user with a given permission may perform an action on a specific resource within the correct business context.

Authorization must consider:

- Assigned permissions
- Tenant scope
- Resource ownership
- Applicable business rules

Policies are the appropriate place to combine permission checks with business authorization context.

### Principle of Least Privilege

YachtOS follows the **principle of least privilege**.

Users must receive only the permissions required to perform their responsibilities.

Least privilege reduces security risk, limits accidental access and supports clearer operational accountability in a multi-tenant SaaS environment.

Default access must be restrictive.

Additional permissions must be granted deliberately through role assignment rather than assumed broadly.

### Why Authorization Is Independent from the Users Module

Authorization must remain outside the Users module because identity and access control are different concerns.

The Users module owns:

- Identity
- Profile information
- User status and lifecycle

Authorization owns:

- Roles
- Permissions
- Access decisions
- Policy enforcement

These concerns must remain separate because:

- Profile data changes for different reasons than access rules.
- Permission models evolve independently from user identity.
- Access control depends on tenant and operational context, not on profile fields.
- Mixing authorization into the Users module would violate module boundaries established by ADR-0007 and ADR-0008.
- A commercial SaaS platform requires a centralized and auditable authorization model shared across all modules.

The Users module provides identity records that authorization logic may reference.

It does not determine what actions a user is allowed to perform.

---

## Consequences

### Positive

- Clear separation between identity and access control.
- Consistent authorization behavior across all business modules.
- Roles and permissions can evolve without changing the user identity model.
- Policy-based authorization improves maintainability and auditability.
- Least privilege reduces unnecessary access across tenants and modules.
- The authorization model supports long-term SaaS growth and future role customization.

### Negative

- Authorization requires its own domain concepts beyond user identity.
- Developers must understand roles, permissions and policies in addition to user records.
- Cross-module authorization rules require disciplined policy design.
- Additional architectural decisions will be required for persistence and enforcement details.

---

## Alternatives Considered

### Permission Flags Stored on User Records

Store permissions or roles directly on the user profile.

Rejected because:

- Identity becomes coupled to access control rules.
- Permission changes would require profile mutation.
- The model conflicts with ADR-0007.
- Access control would become difficult to audit and scale.

### Authorization Embedded in Controllers or Modules

Implement permission checks ad hoc within each module without a shared RBAC model.

Rejected because:

- Authorization behavior would become inconsistent.
- Duplicate permission logic would increase maintenance cost.
- Policy review and auditing would become difficult.
- SaaS-wide access control would lack a single architectural foundation.

### Access Control List Only Model

Assign permissions directly to each user without roles.

Rejected because:

- Administration becomes difficult as the number of users and permissions grows.
- Role-based grouping provides clearer operational meaning.
- Tenant administration would become harder to manage at scale.
- RBAC provides a better balance of clarity and scalability for YachtOS.

### Users Module Owns Authorization

Manage roles and permissions inside the Users module.

Rejected because:

- Module boundaries between identity and access control would be lost.
- Authorization would become tied to profile lifecycle changes.
- Other modules would depend on Users for access decisions beyond identity needs.
- A dedicated authorization model is required for a multi-module SaaS platform.

---

## Final Decision

YachtOS will use **Role-Based Access Control (RBAC)** as its authorization model.

Authorization will be based on:

- **Roles** that group responsibilities
- **Permissions** that define allowed actions
- **Policies** that enforce business authorization decisions

YachtOS will follow the **principle of least privilege**.

Authorization will remain independent from the Users module.

The Users module owns identity.

Authorization owns access control.

This separation is required for secure, maintainable and scalable commercial SaaS operation in YachtOS.

Authentication, membership relationships and persistence details will be defined in separate architectural decisions.
