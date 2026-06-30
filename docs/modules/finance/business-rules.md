# Finance Business Rules

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the business rules for the Finance module.

---

## Business Rules

### BR-001

Every financial record must belong to exactly one tenant.

---

### BR-002

Every financial record must belong to exactly one yacht.

---

### BR-003

Every expense must belong to one expense category.

---

### BR-004

Every expense must have a transaction date.

---

### BR-005

Every expense must have an amount greater than zero.

---

### BR-006

Expenses may include one or more attachments such as receipts or invoices.

---

### BR-007

Expenses may require approval before they become final.

Approval requirements are determined by tenant policies.

---

### BR-008

Approved expenses become read-only except for authorized administrative corrections.

---

### BR-009

Every financial transaction must be traceable to the user who created or approved it.

---

### BR-010

Financial history must never be permanently deleted.

---

### BR-011

Users cannot access financial records belonging to another tenant.

---

### BR-012

Archiving a yacht must not remove its financial history.

---

### BR-013

Budget monitoring is informational.

The system may warn users when budgets are exceeded but must not prevent expense creation unless configured by tenant policies.

---

## Validation Rules

The system must validate:

- Tenant ownership
- Yacht existence
- Expense category
- Expense amount
- Transaction date
- User permissions
- Approval workflow