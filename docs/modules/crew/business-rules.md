# Crew Business Rules

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the business rules for the Crew module.

---

## Business Rules

### BR-001

Every crew assignment must belong to exactly one yacht.

---

### BR-002

Every crew assignment must belong to exactly one user.

---

### BR-003

A user may serve on multiple yachts over time.

Historical assignments must always be preserved.

---

### BR-004

A crew member may only have one active assignment on the same yacht at the same time.

---

### BR-005

Crew members are assigned through the Crew module.

Platform users are managed by the Users module.

---

### BR-006

Crew assignments must have a start date.

An end date is optional until the assignment is completed.

---

### BR-007

Crew certifications may have expiration dates.

Expired certifications must be identifiable by the system.

---

### BR-008

Removing a crew member from a yacht must not delete historical operational records.

---

### BR-009

Crew members cannot access yachts outside their tenant.

---

### BR-010

A yacht may operate temporarily with incomplete crew assignments.

The system must not require every possible crew position to be filled.

---

## Validation Rules

The system must validate:

- User existence
- Yacht existence
- Tenant ownership
- Assignment dates
- Active assignment conflicts