# Inventory Business Rules

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the business rules for the Inventory module.

---

## Business Rules

### BR-001

Every inventory item must belong to exactly one yacht.

---

### BR-002

Every inventory item must belong to exactly one tenant.

---

### BR-003

Every inventory item must belong to one inventory category.

---

### BR-004

Stock quantity must never become negative.

The system must reject stock movements that would result in negative inventory.

---

### BR-005

Every stock movement must be recorded.

Inventory history must always be preserved.

---

### BR-006

Inventory adjustments must include a reason.

---

### BR-007

Inventory items may define a minimum stock level.

The system should identify items below the configured minimum level.

---

### BR-008

Inventory items may be assigned to a storage location.

---

### BR-009

Inventory history must remain available even after an item is archived.

---

### BR-010

Inventory items cannot be permanently deleted if they have stock movement history.

---

### BR-011

Users cannot access inventory belonging to another tenant.

---

### BR-012

Every stock movement must be associated with the user who performed the operation.

---

## Validation Rules

The system must validate:

- Tenant ownership
- Yacht existence
- Inventory item existence
- Stock availability
- User permissions
- Stock movement consistency