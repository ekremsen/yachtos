# Finance Acceptance Criteria

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the acceptance criteria for the Finance module.

The module is considered complete only when all criteria below are satisfied.

---

## Functional Acceptance Criteria

### AC-001

Authorized users can create expenses.

---

### AC-002

Every expense must belong to exactly one yacht.

---

### AC-003

Every expense must belong to exactly one tenant.

---

### AC-004

Every expense must belong to one expense category.

---

### AC-005

Expenses can be updated while they are editable.

---

### AC-006

Expenses can be approved or rejected according to tenant policies.

---

### AC-007

Approved expenses become read-only except for authorized administrative corrections.

---

### AC-008

Users can upload and manage expense attachments.

---

### AC-009

Users cannot access financial records belonging to another tenant.

---

### AC-010

Financial history remains available even if the related yacht is archived.

---

## Non-Functional Acceptance Criteria

### AC-011

The Finance module must comply with YachtOS REST API standards.

---

### AC-012

The Finance module must comply with tenant isolation rules.

---

### AC-013

The Finance module must be fully responsive.

---

### AC-014

Creating an expense should take less than one minute for an experienced user.

---

### AC-015

The module must preserve complete financial history for audit purposes.

---

## Completion Criteria

The Finance module is considered complete when:

- All business rules are implemented.
- All database requirements are implemented.
- All API endpoints are implemented.
- All UI requirements are implemented.
- All acceptance criteria are satisfied.
- Unit tests pass.
- Integration tests pass.
- Documentation is complete and up to date.