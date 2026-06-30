# Charter Acceptance Criteria

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the acceptance criteria for the Charter module.

The module is considered complete only when all criteria below are satisfied.

---

## Functional Acceptance Criteria

### AC-001

Authorized users can create charter bookings.

---

### AC-002

Every charter booking must belong to exactly one tenant.

---

### AC-003

Every charter booking must belong to exactly one yacht.

---

### AC-004

The system prevents overlapping confirmed charter bookings for the same yacht.

---

### AC-005

Users can update charter bookings while they are editable.

---

### AC-006

Users can confirm and cancel charter bookings.

---

### AC-007

Users can manage guests assigned to a charter booking.

---

### AC-008

Users can record charter payments.

---

### AC-009

Users cannot access charter records belonging to another tenant.

---

### AC-010

Completed and cancelled charter bookings remain available for historical reporting.

---

## Non-Functional Acceptance Criteria

### AC-011

The Charter module must comply with YachtOS REST API standards.

---

### AC-012

The Charter module must comply with tenant isolation rules.

---

### AC-013

The Charter module must be fully responsive.

---

### AC-014

Creating a charter booking should take less than two minutes for an experienced user.

---

### AC-015

The system must detect booking conflicts before a booking is confirmed.

---

## Completion Criteria

The Charter module is considered complete when:

- All business rules are implemented.
- All database requirements are implemented.
- All API endpoints are implemented.
- All UI requirements are implemented.
- All acceptance criteria are satisfied.
- Unit tests pass.
- Integration tests pass.
- Documentation is complete and up to date.