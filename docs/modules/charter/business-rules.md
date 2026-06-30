# Charter Business Rules

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the business rules for the Charter module.

The Charter module extends YachtOS for commercial charter operations without affecting the core operational architecture.

---

## Business Rules

### BR-001

Every charter booking must belong to exactly one tenant.

---

### BR-002

Every charter booking must belong to exactly one yacht.

---

### BR-003

A yacht cannot have overlapping confirmed charter bookings.

---

### BR-004

Every charter booking must have a start date and an end date.

The end date must always be later than the start date.

---

### BR-005

Every charter booking must have a status.

Supported statuses include:

- Draft
- Pending
- Confirmed
- In Progress
- Completed
- Cancelled

---

### BR-006

A charter booking may include one or more guests.

---

### BR-007

Guest information must remain available for historical reporting.

---

### BR-008

Cancelling a charter booking must not remove historical operational data.

---

### BR-009

Charter bookings must remain linked to the yacht even after completion.

---

### BR-010

Users cannot access charter bookings belonging to another tenant.

---

### BR-011

Commercial workflows must not modify the core yacht operation model.

Charter functionality extends YachtOS but does not replace operational modules.

---

## Validation Rules

The system must validate:

- Tenant ownership
- Yacht availability
- Booking dates
- User permissions
- Booking status transitions