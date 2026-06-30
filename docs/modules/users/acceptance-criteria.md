# Users Acceptance Criteria

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the acceptance criteria for the Users module.

The module is considered complete only when all criteria below are satisfied.

---

## Functional Acceptance Criteria

### AC-001

Authorized users can create new platform users.

---

### AC-002

Each user must have a unique email address.

---

### AC-003

Users can update their profile information.

---

### AC-004

Users can be archived.

Archived users can no longer access YachtOS.

---

### AC-005

Users cannot access data outside their tenant.

---

### AC-006

Yacht responsibilities are managed through Yacht Memberships.

The Users module must never store Owner, Captain, Crew or Engineer roles directly.

---

### AC-007

Users can exist without being assigned to a yacht.

---

### AC-008

Historical user records remain available after the user leaves a yacht or organization.

---

## Non-Functional Acceptance Criteria

### AC-009

The module must comply with YachtOS REST API standards.

---

### AC-010

The module must comply with tenant isolation rules.

---

### AC-011

The module must be fully responsive.

---

### AC-012

User creation should be completed in less than one minute by an experienced administrator.

---

## Completion Criteria

The Users module is considered complete when:

- All business rules are implemented.
- All database requirements are implemented.
- All API endpoints are implemented.
- All UI requirements are implemented.
- All acceptance criteria are satisfied.
- Unit tests pass.
- Integration tests pass.
- Documentation is complete and up to date.