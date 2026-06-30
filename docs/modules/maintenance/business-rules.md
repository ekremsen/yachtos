# Maintenance Business Rules

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the business rules for the Maintenance module.

---

## Business Rules

### BR-001

Every maintenance record must belong to exactly one yacht.

---

### BR-002

Every maintenance record must belong to exactly one tenant.

---

### BR-003

Maintenance records must remain available for the entire operational lifecycle of the yacht.

---

### BR-004

A maintenance record must always have a status.

Supported statuses include:

- Planned
- In Progress
- Completed
- Cancelled

---

### BR-005

Completed maintenance records must not be permanently deleted.

---

### BR-006

Maintenance completion date cannot be earlier than the scheduled date.

---

### BR-007

Maintenance may be assigned to one or more responsible users.

---

### BR-008

Maintenance records may include notes and file attachments.

---

### BR-009

A completed maintenance record becomes read-only except for administrative corrections.

---

### BR-010

Every maintenance action must be recorded in the audit history.

---

### BR-011

Users cannot access maintenance records belonging to another tenant.

---

### BR-012

Maintenance history must remain available even if the yacht is archived.

---

## Validation Rules

The system must validate:

- Yacht existence
- Tenant ownership
- User permissions
- Maintenance status transitions
- Maintenance dates