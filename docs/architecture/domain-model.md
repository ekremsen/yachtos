# Domain Model

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 02 July 2026

---

## Purpose

This document defines the core domain model of YachtOS.

It establishes the foundational entities, relationships and boundaries that every business module must follow.

This document resolves ownership questions between modules and provides a single reference for how identity, tenancy, yacht operations and access control fit together.

---

## Domain Principles

- **Yacht** is the operational center of YachtOS.
- **Tenant** is the data isolation boundary.
- **User** is an identity and profile record, not a business role.
- Operational roles such as Owner, Captain, Crew and Engineer must never be stored directly on the user record.
- Long-term SaaS growth is supported through **membership-based relationships**, not through hard-coded role columns on core entities.
- **Authentication** answers: "Who are you?"
- **Authorization** answers: "What can you do?"

---

## Domain Overview

```text
Tenant
├── Tenant Membership ──► User
└── Yacht
     ├── Yacht Membership ──► User
     └── Crew Assignment ──► User

Authentication ──► User identity verification
Authorization ──► Role + Permission enforcement within Tenant context
```

Every operational record in YachtOS belongs to exactly one tenant and is typically linked to one yacht.

---

## Core Entities

### Tenant

A **Tenant** represents a yacht operating organization using YachtOS.

Examples include private yacht owners, charter companies, yacht management companies and fleet operators.

#### Responsibilities

- Organizational boundary for all business data
- Data isolation between organizations
- Tenant-level settings such as timezone and currency

#### Module Ownership

The **Tenants module** owns tenant organization data.

The Tenants module must **not** duplicate or own the `yachts` table schema. Yacht data belongs to the Yachts module.

#### Key Rules

- Every business record belongs to exactly one tenant.
- Cross-tenant data access is strictly prohibited.
- Tenants are archived or deactivated instead of permanently deleted.

---

### Yacht

A **Yacht** is the primary operational workspace of YachtOS.

Every operational activity — maintenance, inventory, finance, crew operations and future charter workflows — is centered around a yacht.

#### Responsibilities

- Yacht identity and specifications
- Operational status and lifecycle
- Anchor point for all yacht-scoped business records

#### Module Ownership

The **Yachts module** owns the `yachts` table and yacht lifecycle management.

#### Key Rules

- Every yacht belongs to exactly one tenant.
- A yacht must never be permanently deleted.
- Owner, Captain, Crew and Engineer relationships must not be stored directly on the yacht record.
- Yacht assignments and operational roles are managed through membership records.

---

### User

A **User** represents a person who can access YachtOS.

The Users module manages identity-related profile information such as name, email, contact details, preferences and account status.

A user is **not** a yacht role.

#### Responsibilities

- User profile and personal information
- Contact information and preferences
- User lifecycle and status

#### What Users Are Not

The Users module is **not** responsible for:

- Authentication
- Authorization
- Tenant membership
- Yacht membership
- Crew assignment
- Operational role storage

#### Key Rules

- Every person who can access YachtOS must have a user record.
- A user may exist before being assigned to a yacht or tenant membership.
- User history must be preserved when linked to historical operational records.
- Owner, Captain, Crew and Engineer must never be stored as columns or attributes on the user record.

#### Open Decision

The physical storage of tenant association on the `users` table — for example through a `tenant_id` column — is **not decided in this document**.

Tenant association is modeled through **Tenant Membership** regardless of how the user record is stored.

---

## Membership Entities

Membership records connect users to organizations and yachts over time.

Membership-based relationships are preferred because they preserve history, support SaaS growth and avoid coupling identity to a single operational role.

---

### Tenant Membership

A **Tenant Membership** represents the relationship between a user and a tenant organization.

It defines that a user belongs to an organization and may access tenant-scoped data according to authorization rules.

#### Typical Attributes

- tenant_id
- user_id
- status
- start_date
- end_date

#### Purpose

- Connect users to organizations
- Support users who may belong to one tenant in the current product scope
- Preserve membership history over time
- Provide the tenant context required after authentication

#### Key Rules

- A user accesses tenant data only through a valid tenant membership.
- Membership history must be preserved.
- Tenant membership is separate from yacht membership and crew assignment.

---

### Yacht Membership

A **Yacht Membership** represents the operational role relationship between a user and a yacht.

It defines responsibilities such as ownership and command structure on a specific yacht.

#### Typical Attributes

- tenant_id
- yacht_id
- user_id
- role
- start_date
- end_date
- status

#### Supported Roles

- Owner
- Captain
- Crew
- Engineer

#### Purpose

- Define who is responsible for a yacht in an operational sense
- Preserve historical owner, captain and crew relationships
- Support authorization decisions that depend on yacht-level responsibility

#### Key Rules

- Yacht roles must never be stored on the user record or yacht record.
- A user may have multiple yacht memberships over time.
- Historical yacht memberships must never be permanently deleted.
- A yacht may require at least one active Owner membership according to yacht business rules.

#### Distinction from Crew Assignment

**Yacht Membership** answers:

"What operational role does this user hold on this yacht?"

It is concerned with responsibility, authority and yacht-level role history.

---

### Crew Assignment

A **Crew Assignment** represents the operational employment or service relationship of a user on a yacht.

It is focused on crew operations, not platform identity.

#### Typical Attributes

- tenant_id
- yacht_id
- user_id
- position
- employment_type
- start_date
- end_date
- status

#### Purpose

- Track who is serving on a yacht in an operational crew context
- Manage crew history, certifications and employment-related information
- Support day-to-day crew operations without replacing yacht membership roles

#### Key Rules

- A crew assignment always references an existing user and yacht.
- A user may have multiple crew assignments over time.
- Historical crew assignments must be preserved.
- Crew assignment does not replace yacht membership.

#### Distinction from Yacht Membership

**Crew Assignment** answers:

"How is this user operationally assigned to work on this yacht?"

It is concerned with crew operations, employment context, certifications and assignment history.

A user may simultaneously hold:

- a **Yacht Membership** role such as Captain, and
- a **Crew Assignment** record with position and employment details.

These are separate concepts and must remain separate in data and module ownership.

---

## Access Control Concepts

Authentication and authorization are separate concerns.

They must not be implemented inside the Users module.

---

### Authentication

**Authentication** verifies identity.

It answers:

**"Who are you?"**

#### Responsibilities

- Validate credentials
- Verify user status
- Issue and validate access tokens
- Determine the authenticated user identity

#### What Authentication Does Not Do

Authentication does **not** decide:

- Tenant access by itself
- Yacht access by itself
- Business permissions
- Operational role meaning

Authentication confirms identity only.

Tenant context and permissions are resolved through membership and authorization layers.

---

### Authorization

**Authorization** verifies permissions.

It answers:

**"What can you do?"**

#### Responsibilities

- Evaluate permissions within tenant context
- Validate access to resources
- Enforce policy-based business authorization
- Consider yacht context when applicable

#### Authorization Inputs

Every authorization decision should consider:

- Authenticated user
- Active tenant membership
- Assigned roles
- Resolved permissions
- Resource ownership
- Relevant yacht membership or crew context
- Applicable business rules

Permission alone is not sufficient if the resource belongs to another tenant or yacht outside the user's scope.

---

### Role

A **Role** represents a named set of responsibilities within a tenant.

Roles group permissions and simplify administration.

#### Examples

- Administrator
- Owner
- Captain
- Engineer
- Crew

#### Key Rules

- Roles belong to the authorization model, not to the Users profile model.
- Roles must not be stored as direct attributes on the user record.
- A user receives roles through membership and authorization assignments.
- Operational yacht roles such as Owner and Captain are expressed through **Yacht Membership**.
- Tenant administration roles such as Administrator are expressed through **Tenant Membership** and authorization configuration.

---

### Permission

A **Permission** represents a specific allowed action on a resource or domain.

Permissions should be action-based and independent of user interface structure.

#### Examples

- users.view
- users.create
- yachts.update
- maintenance.complete
- expenses.approve

#### Key Rules

- Permissions are assigned to roles.
- Users receive permissions through roles.
- Authorization checks must validate permission, tenant ownership and resource scope together.
- Permissions must remain stable even when UI or API presentation changes.

---

## Entity Relationships

```text
Tenant
│
├── Tenant Membership
│      └── User
│
└── Yacht
       │
       ├── Yacht Membership
       │      └── User
       │
       └── Crew Assignment
              └── User

User
├── Authenticated by Authentication
└── Authorized by Authorization using Roles and Permissions
```

---

## Module Ownership Summary

| Concept | Primary Module | Notes |
|---------|----------------|-------|
| Tenant | Tenants | Organization and tenant settings only |
| Yacht | Yachts | Owns the `yachts` table |
| User | Users | Identity and profile only |
| Tenant Membership | Tenants | Organization access relationship |
| Yacht Membership | Yachts or dedicated membership module | Operational yacht roles |
| Crew Assignment | Crew | Operational crew service relationship |
| Authentication | Support / Auth | Identity verification only |
| Authorization | Support / Auth | Roles, permissions and policies |
| Role | Support / Auth | Named permission grouping |
| Permission | Support / Auth | Action-based access control |

---

## Design Rules

### Identity Rules

- User records store identity and profile data only.
- Never store Owner, Captain, Crew or Engineer directly on users.
- Never store business roles directly on yachts.

### Tenancy Rules

- Tenant is the isolation boundary for all business data.
- Every business query must execute within tenant context.
- Cross-tenant access is prohibited.

### Yacht Rules

- Yacht is the operational center of the platform.
- Every operational module should link to a yacht when applicable.
- The Yachts module owns yacht schema and lifecycle.

### Membership Rules

- Prefer membership-based relationships over embedded role columns.
- Preserve membership and assignment history.
- Do not merge Tenant Membership, Yacht Membership and Crew Assignment into one table.

### Access Control Rules

- Authentication verifies identity.
- Authorization verifies permissions.
- Roles group permissions.
- Permissions remain independent from UI structure.

---

## Non-Goals

This document does not define:

- Final database column lists for every table
- Whether `users.tenant_id` will exist
- API endpoint contracts
- UI workflows
- Guest records
- Charter bookings
- Document or task modules

Those concerns are defined in module-specific documentation.

---

## Related Documents

- tenancy.md
- authentication.md
- authorization.md
- database.md
- ../modules/users/index.md
- ../modules/tenants/index.md
- ../modules/yachts/index.md
- ../modules/crew/index.md

---

## Domain Principle

YachtOS separates **identity**, **organization**, **operational responsibility** and **access control**.

Users identify people.

Tenants isolate organizations.

Yachts anchor operations.

Memberships connect people to organizations and yachts over time.

Authentication verifies identity.

Authorization enforces what authenticated users are allowed to do.
