# Coding Standards

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the coding standards used throughout YachtOS.

Every contributor must follow these standards to ensure consistency across the project.

---

## General Rules

- Prefer readability over cleverness.
- Keep code simple.
- Avoid duplicate logic.
- Write self-explanatory code.
- Optimize only when necessary.

---

## Naming Conventions

### Files

- kebab-case

Example:

```
user-profile.tsx
maintenance-service.php
```

### Classes

- PascalCase

Example:

```
UserService
MaintenanceRepository
```

### Variables

- camelCase

Example:

```php
$userName
$maintenanceDate
```

### Database

- snake_case

Example:

```
user_profiles
maintenance_tasks
guest_preferences
```

### API Endpoints

- kebab-case

Example:

```
GET /maintenance-tasks
POST /guest-preferences
```

---

## Controller Rule

Controllers should only:

- Validate requests
- Call services
- Return responses

Business logic must never exist inside controllers.

---

## Comments

Write comments only when they explain **why**, not **what**.

Bad:

```php
// Increment counter
$count++;
```

Good:

```php
// Required to preserve sequential maintenance numbers
$count++;
```

---

## Naming Philosophy

Naming decisions must be made carefully at the beginning of the project.

Inconsistent or unclear naming creates long-term friction and reduces developer confidence.

YachtOS follows global best practices and prioritizes consistency over personal preference.

Once naming standards are accepted, they should not be changed without a strong architectural reason.

---

## Naming Standards

| Context | Standard | Example |
|---------|----------|---------|
| Module folders | plural or domain noun | users, yachts, tenants, crew, inventory |
| Database tables | plural | users, yachts, tenants |
| API endpoints | plural | /api/users, /api/yachts |
| Models | singular PascalCase | User, Yacht, Tenant |
| Services | singular PascalCase | UserService, YachtService |
| Controllers | singular PascalCase | UserController |
| Variables | singular camelCase | user, yacht |
| Collections | plural camelCase | users, yachts |
| Database columns | snake_case | created_at, tenant_id |

---

## Final Principle

Consistency is more valuable than personal preference.