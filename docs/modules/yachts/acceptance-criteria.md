# Yacht Acceptance Criteria

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the acceptance criteria for the Yacht module.

The module is considered complete only when all criteria below are satisfied.

---

## Functional Acceptance Criteria

### AC-001

Users with the appropriate permission can create a yacht.

---

### AC-002

Every newly created yacht is assigned to exactly one tenant.

---

### AC-003

A yacht can be updated using the Yacht module.

---

### AC-004

A yacht can be archived.

Archived yachts remain available for historical reporting.

---

### AC-005

Users cannot access yachts belonging to another tenant.

---

### AC-006

Owner, Captain and Crew assignments are managed through yacht memberships.

They are never stored directly on the yacht record.

---

### AC-007

Every operational module references a yacht.

Examples include:

- Maintenance
- Inventory
- Tasks
- Documents
- Expenses
- Guest Preferences

---

### AC-008

Historical yacht data remains available after ownership changes.

---

## Non-Functional Acceptance Criteria

### AC-009

The Yacht module must be fully responsive.

---

### AC-010

Yacht creation should be completed within one minute by an experienced user.

---

### AC-011

The module must follow YachtOS REST API conventions.

---

### AC-012

The module must comply with YachtOS security, authorization and tenant isolation rules.

---

## Completion Criteria

The Yacht module is considered complete when:

- All business rules are implemented.
- All acceptance criteria are satisfied.
- All APIs are documented.
- Database design is implemented.
- UI requirements are implemented.
- Unit and integration tests pass.
- Documentation is up to date.