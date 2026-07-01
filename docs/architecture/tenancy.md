# Tenancy Architecture

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines how multi-tenancy is implemented in YachtOS.

Tenant isolation is one of the core architectural principles of the platform.

Every business operation must execute within the context of a tenant.

---

## Tenancy Model

YachtOS uses a **Shared Database, Shared Schema** multi-tenant architecture.

All tenants share the same database while business data is isolated using the `tenant_id` column.

---

## Tenant Definition

A tenant represents an organization using YachtOS.

Examples include:

- Private yacht owner
- Charter company
- Yacht management company
- Fleet operator

Every tenant operates independently.

---

## Tenant Isolation

Every business record must belong to exactly one tenant.

Every authenticated request must execute within the current tenant context.

Cross-tenant access is strictly prohibited.

---

## Tenant Context

After authentication, the application determines the active tenant.

Every business query must automatically apply tenant filtering.

Example:

Current Tenant

Tenant A

↓

Allowed

- Users
- Yachts
- Crew
- Inventory
- Maintenance
- Finance

↓

Only records belonging to Tenant A

---

## Database Rules

Business tables should contain:

- tenant_id

System tables that are shared across the platform may not require a tenant identifier.

---

## Query Rules

Business queries must never manually filter tenants throughout the application.

Tenant filtering should be handled centrally.

Possible implementations include:

- Global query scopes
- Repository layer
- Dedicated tenant middleware

The implementation detail may evolve without changing the business architecture.

---

## Authorization

Authorization must always consider:

- Authenticated user
- Active tenant
- Resource ownership
- Assigned permissions

A valid permission is not sufficient if the resource belongs to another tenant.

---

## Tenant Lifecycle

A tenant may be:

- Active
- Suspended
- Archived

Archived tenants retain historical business data.

Business records must never be reassigned to another tenant.

---

## Future Compatibility

The tenancy architecture supports future expansion for:

- Multiple yachts per tenant
- Multiple offices
- Fleet operators
- Charter companies
- White-label deployments

No structural database changes should be required for these scenarios.

---

## Non-Goals

The initial tenancy architecture will not include:

- Separate databases per tenant
- Separate schemas per tenant
- Cross-tenant reporting
- Tenant-to-tenant data sharing

These capabilities may be introduced only when business requirements justify them.

---

## Tenancy Principle

Every business operation belongs to exactly one tenant.

Tenant isolation is enforced by architecture, not by developer discipline.