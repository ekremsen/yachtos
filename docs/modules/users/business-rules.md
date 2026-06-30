# Users Business Rules

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the business rules of the Users module.

---

## Business Rules

### BR-001

Every person who can access YachtOS must have a user record.

---

### BR-002

A user represents a platform account, not a yacht role.

---

### BR-003

Yacht roles such as Owner, Captain, Crew and Engineer must be managed through yacht memberships.

---

### BR-004

A user may be assigned to multiple yachts over time.

---

### BR-005

User history must be preserved.

Users must not be permanently deleted if they are linked to historical operational records.

---

### BR-006

A user may be archived or deactivated.

Archived users cannot log in or perform actions.

---

### BR-007

A user must have a unique email address.

---

### BR-008

A user profile may exist before being assigned to a yacht.

---

### BR-009

Guests are not platform users unless they need access to YachtOS.

Guest records belong to the future guest-related module.

---

## Validation Rules

The system must validate user status before allowing access or actions.