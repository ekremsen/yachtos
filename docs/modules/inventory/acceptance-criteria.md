# Inventory Acceptance Criteria

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the acceptance criteria for the Inventory module.

The module is considered complete only when all criteria below are satisfied.

---

## Functional Acceptance Criteria

### AC-001

Authorized users can create inventory items.

---

### AC-002

Every inventory item must belong to exactly one yacht.

---

### AC-003

Every inventory item must belong to exactly one tenant.

---

### AC-004

Inventory items can be updated.

---

### AC-005

Inventory items can be archived.

Archived inventory items remain available for historical reporting.

---

### AC-006

Authorized users can record stock movements.

---

### AC-007

The system prevents stock quantities from becoming negative.

---

### AC-008

Every stock movement is recorded and remains available in the inventory history.

---

### AC-009

Users cannot access inventory belonging to another tenant.

---

### AC-010

Inventory categories can be created and managed.

---

## Non-Functional Acceptance Criteria

### AC-011

The Inventory module must comply with YachtOS REST API standards.

---

### AC-012

The Inventory module must comply with tenant isolation rules.

---

### AC-013

The Inventory module must be fully responsive.

---

### AC-014

Creating an inventory item should take less than one minute for an experienced user.

---

### AC-015

Recording a stock movement should be completed in less than 30 seconds.

---

## Completion Criteria

The Inventory module is considered complete when:

- All business rules are implemented.
- All database requirements are implemented.
- All API endpoints are implemented.
- All UI requirements are implemented.
- All acceptance criteria are satisfied.
- Unit tests pass.
- Integration tests pass.
- Documentation is complete and up to date.