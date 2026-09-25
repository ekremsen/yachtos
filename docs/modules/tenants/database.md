# Tenant Database Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the database design for the Tenant module.

The Tenant module provides the organizational boundary of YachtOS and ensures complete data isolation between organizations.

---

## Database Overview

The Tenant module owns two core entities under ADR-0008:

- Tenant
- Tenant Membership

Yachts and Yacht Memberships belong to the Yachts module and are not implemented
in the authenticated tenant foundation. The older schema ownership in this draft
is corrected here to match the accepted ADR.

---

## Tables

### tenants

Represents an organization using YachtOS.

#### Columns

- id (UUID primary key)
- name
- slug
- type
- status
- country
- timezone
- currency
- created_at
- updated_at

#### Notes

- One tenant can manage one or many yachts.
- Tenant data must never be shared with another tenant.
- Tenants are archived instead of permanently deleted.

---

### tenant_memberships

Represents the historical organization relationship between a user and a tenant.

#### Columns

- id (UUID primary key)
- tenant_id (UUID foreign key; restrict deletion)
- user_id (UUID foreign key; restrict deletion)
- start_date
- end_date
- status
- created_at
- updated_at

#### Notes

- A user may have multiple historical tenant memberships over time.
- Membership history must always be preserved.
- Access requires exactly one currently valid active membership and an active tenant.
- Memberships have `active`, `inactive` or `archived` status, defaulting to `inactive`.
- Start date is inclusive; end date is exclusive; validity uses the current UTC date.
- Multiple current memberships fail closed, including duplicates for one tenant.
- An index covers user/status/start/end lookup, and tenant_id is indexed.
- No organization or yacht roles are stored here in this foundation increment; RBAC is deferred.

---

## Relationships

```text
Tenant
│
└── Tenant Membership
       └── User
```

---

## Design Rules

- Every tenant membership references one tenant and one user.
- Tenants use `active`, `suspended` or `archived` status; the database default is `suspended`.
- Users and tenants use UUID keys; profile records contain no direct tenant or role column.
- Historical memberships must never be deleted.
- Hard delete is prohibited for business data.
- Data isolation between tenants is mandatory.
- Internal foundation provisioning may be incomplete under [ADR-0011](../../adr/ADR-0011-authenticated-tenant-foundation.md). Active access does not imply completed operational onboarding.
- Yacht and yacht-membership schema ownership remains with Yachts.

---

## Future Considerations

The current database design intentionally supports future expansion for:

- Charter companies
- Fleet operators
- Yacht management companies

No structural database changes should be required to support these business models.
