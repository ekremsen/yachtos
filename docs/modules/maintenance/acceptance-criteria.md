# Maintenance Acceptance Criteria

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the acceptance criteria for the Maintenance module.

The module is considered complete only when all criteria below are satisfied.

---

## Functional Acceptance Criteria

### AC-001

Authorized users can create maintenance records.

---

### AC-002

Every maintenance record must belong to exactly one yacht.

---

### AC-003

Every maintenance record must belong to exactly one tenant.

---

### AC-004

Maintenance records can be updated while they are in an editable status.

---

### AC-005

Maintenance records can be completed.

Completed maintenance records remain available for historical reference.

---

### AC-006

Maintenance records can be cancelled before completion.

---

### AC-007

Users can upload and manage maintenance attachments.

---

### AC-008

Users cannot access maintenance records belonging to another tenant.

---

### AC-009

Maintenance history remains available even if the yacht is archived.

---

### AC-010

The system prevents invalid maintenance status transitions.

---

## Non-Functional Acceptance Criteria

### AC-011

The Maintenance module must comply with YachtOS REST API standards.

---

### AC-012

The Maintenance module must comply with tenant isolation rules.

---

### AC-013

The Maintenance module must be fully responsive.

---

### AC-014

Creating a maintenance record should take less than one minute for an experienced user.

---

## Completion Criteria

The Maintenance module is considered complete when:

- All business rules are implemented.
- All database requirements are implemented.
- All API endpoints are implemented.
- All UI requirements are implemented.
- All acceptance criteria are satisfied.
- Unit tests pass.
- Integration tests pass.
- Documentation is complete and up to date.