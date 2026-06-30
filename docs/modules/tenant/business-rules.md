# Tenant Business Rules

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the business rules of the Tenant module.

A tenant is a technical and organizational boundary used to isolate yacht operation data.

---

## Business Rules

### BR-001

A tenant represents one yacht operating organization.

---

### BR-002

A tenant must have at least one yacht in the MVP.

YachtOS does not support empty operational organizations in the MVP.

---

### BR-003

A yacht belongs to exactly one tenant during an operational ownership period.

---

### BR-004

If a yacht is sold or transferred to a new organization, a new yacht record should be created under the new tenant to preserve historical operational data.

---

### BR-005

Owner, Captain, Crew and Engineer relationships must not be stored directly on the yacht record.

These relationships must be managed through yacht membership records.

---

### BR-006

Users may be connected to yachts through role-based assignments.

A user may leave one yacht and join another yacht without losing historical records.

---

### BR-007

All operational data must belong to exactly one tenant.

Examples:

- Yachts
- Tasks
- Maintenance records
- Inventory items
- Guest records
- Documents
- Expenses

---

### BR-008

Users can never access data belonging to another tenant.

Cross-tenant data access is strictly prohibited.

---

### BR-009

A tenant must have at least one active administrator.

The last active administrator cannot be removed until another administrator exists.

---

### BR-010

Deleting a tenant is not allowed.

Tenants may only be archived or deactivated to preserve historical data.

---

## Charter Readiness

YachtOS must support charter companies at the tenant and yacht operation level.

However, charter-specific features such as bookings, pricing, contracts and payments are not part of the MVP.

---

## Validation Rules

The system must validate tenant ownership before allowing access to any business data.

Failure to validate tenant ownership must deny access.