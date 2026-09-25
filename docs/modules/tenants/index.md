# Tenant Module

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

The Tenant module represents a yacht operating organization within YachtOS.

Every yacht, crew assignment, inventory item, maintenance record and operational
record belongs to exactly one tenant. User identity is connected to organizations
through tenant membership, as established by ADR-0008.

The Tenant module provides complete data isolation between different organizations using the platform.

---

## Business Goal

Allow multiple independent yacht operations to use YachtOS without sharing any data.

Each tenant should experience YachtOS as if it were their own private system.

---

## Business Rules

- Every tenant owns its own data.
- Data must never be accessible across tenants.
- Every yacht belongs to one tenant.
- Users access one active tenant through valid membership in the MVP; identity may exist before membership.
- Every operational record belongs to one tenant.
- Tenant administrators manage their own users and yachts.

---

## Initial Scope

The MVP should allow a tenant to:

- Create an organization
- Update organization information
- Invite users
- Manage yachts
- Manage organization settings

---

## Out of Scope

The MVP will not include:

- Tenant billing
- Subscription management
- White-label customization
- Multiple organizations per user
- Cross-tenant collaboration

These capabilities may be introduced in future versions.

---

## Success Criteria

A captain can create an organization, add a yacht and invite users without requiring platform administrator assistance.
