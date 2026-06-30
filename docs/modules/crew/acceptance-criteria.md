# Crew Acceptance Criteria

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the acceptance criteria for the Crew module.

The module is considered complete only when all criteria below are satisfied.

---

## Functional Acceptance Criteria

### AC-001

Authorized users can assign a crew member to a yacht.

---

### AC-002

A crew assignment must always reference an existing user and yacht.

---

### AC-003

A crew member cannot have multiple active assignments on the same yacht.

---

### AC-004

Crew assignments can be updated without affecting historical records.

---

### AC-005

Crew assignments can be ended while preserving assignment history.

---

### AC-006

Crew certifications can be created and updated.

---

### AC-007

Expired certifications are clearly identified.

---

### AC-008

Users cannot access crew assignments belonging to another tenant.

---

### AC-009

Historical crew assignments remain available after completion.

---

## Non-Functional Acceptance Criteria

### AC-010

The Crew module must comply with YachtOS REST API standards.

---

### AC-011

The Crew module must comply with tenant isolation rules.

---

### AC-012

The Crew module must be fully responsive.

---

### AC-013

Crew assignment should be completed in less than one minute by an experienced administrator.

---

## Completion Criteria

The Crew module is considered complete when:

- All business rules are implemented.
- All database requirements are implemented.
- All API endpoints are implemented.
- All UI requirements are implemented.
- All acceptance criteria are satisfied.
- Unit tests pass.
- Integration tests pass.
- Documentation is complete and up to date.