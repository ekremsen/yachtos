# Authorization Architecture

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the authorization architecture of YachtOS.

Authorization determines what an authenticated user is allowed to do within the platform.

Authentication verifies identity.

Authorization verifies permissions.

---

## Authorization Strategy

YachtOS uses Role-Based Access Control (RBAC).

Permissions are assigned to roles.

Users receive permissions through their assigned roles.

Additional user-specific permissions may be introduced in the future.

---

## Authorization Flow

Every protected request follows this process:

1. Authenticate the user.
2. Determine the active tenant.
3. Verify the user's status.
4. Load assigned roles.
5. Resolve permissions.
6. Validate access to the requested resource.
7. Execute the requested operation.

Access is denied if any step fails.

---

## Permission Model

Permissions should be action-based.

Examples include:

- users.view
- users.create
- users.update
- users.archive

- yachts.view
- yachts.create
- yachts.update

- maintenance.complete

- expenses.approve

Permissions should remain independent of user interface elements.

---

## Roles

Roles represent responsibilities within a tenant.

Examples include:

- Owner
- Captain
- Engineer
- Crew
- Administrator

Future versions may allow tenants to create custom roles.

---

## Resource Ownership

Permission alone is not sufficient.

The system must also verify:

- Tenant ownership
- Resource ownership
- Business rules

Example:

A user with `maintenance.update` permission cannot update maintenance records belonging to another tenant.

---

## Policy-Based Authorization

Business authorization should be implemented using policies.

Policies centralize authorization logic and keep controllers clean.

---

## Controller Responsibilities

Controllers must never contain permission logic.

Controllers should delegate authorization to:

- Policies
- Authorization services
- Middleware

---

## Default Principle

YachtOS follows the principle of least privilege.

Users receive only the permissions required to perform their responsibilities.

---

## Auditability

Authorization decisions for critical business operations should be auditable.

Examples include:

- Expense approvals
- Charter confirmations
- Crew assignments
- User administration

---

## Future Compatibility

The authorization architecture supports future expansion for:

- Custom roles
- Permission inheritance
- Temporary permissions
- Delegated administration
- External identity providers

---

## Authorization Principle

Authentication answers:

"Who are you?"

Authorization answers:

"What are you allowed to do?"