# Yacht Business Rules

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the business rules of the Yacht module.

These rules govern how yachts are created, managed and transitioned throughout their operational lifecycle.

---

## Business Rules

### BR-001

Every yacht must belong to exactly one tenant.

---

### BR-002

A tenant may manage one or multiple yachts.

---

### BR-003

A yacht cannot exist without an owner.

At least one active user with the **Owner** role must always be assigned to the yacht.

---

### BR-004

A yacht may operate without an assigned captain temporarily.

This allows ownership transfers and recruitment periods without blocking the system.

---

### BR-005

Owner, Captain and Crew relationships must be managed through yacht memberships.

They must never be stored directly on the yacht record.

---

### BR-006

A yacht may have multiple historical owners and captains.

Historical assignments must always be preserved.

---

### BR-007

A yacht must never be permanently deleted.

Archived yachts remain available for historical reporting and auditing.

---

### BR-008

When ownership changes, the operational history must remain preserved.

---

### BR-009

Every operational record must always be linked to a yacht.

Examples include:

- Maintenance
- Inventory
- Tasks
- Guests
- Expenses
- Documents

---

### BR-010

A yacht may be operated as:

- Private yacht
- Charter yacht
- Managed yacht

The operational model must not affect the core Yacht module.

Commercial workflows are handled by their respective modules.

---

## Validation Rules

The system must verify:

- Tenant ownership
- Yacht status
- User permissions

before allowing any modification to yacht data.