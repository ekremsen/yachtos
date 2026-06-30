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

The Tenant module is built around three core entities:

- Tenant
- Yacht
- Yacht Member

These entities establish the foundation for all other business modules.

---

## Tables

### tenants

Represents an organization using YachtOS.

#### Columns

- id
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

### yachts

Represents a yacht managed by a tenant.

#### Columns

- id
- tenant_id
- name
- registration_number
- flag_country
- home_port
- yacht_type
- status
- created_at
- updated_at

#### Notes

- Every yacht belongs to exactly one tenant.
- A yacht is the operational center of YachtOS.
- Owner and Captain information must not be stored in this table.

---

### yacht_members

Represents the relationship between users and yachts.

#### Columns

- id
- tenant_id
- yacht_id
- user_id
- role
- start_date
- end_date
- status
- created_at
- updated_at

#### Notes

- A user may have multiple yacht memberships over time.
- Membership history must always be preserved.
- Active memberships define current responsibilities.
- Supported roles include:
  - Owner
  - Captain
  - Crew
  - Engineer

---

## Relationships

```text
Tenant
│
└── Yacht
     │
     └── Yacht Member
            │
            └── User
```

---

## Design Rules

- Every yacht belongs to exactly one tenant.
- Every yacht member belongs to exactly one tenant.
- Every yacht member belongs to exactly one yacht.
- Owner and Captain relationships are managed through `yacht_members`.
- Historical memberships must never be deleted.
- Historical yacht records must never be deleted.
- Hard delete is prohibited for business data.
- Data isolation between tenants is mandatory.

---

## Future Considerations

The current database design intentionally supports future expansion for:

- Charter companies
- Fleet operators
- Yacht management companies

No structural database changes should be required to support these business models.