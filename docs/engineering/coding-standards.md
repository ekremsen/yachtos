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

## Final Principle

Consistency is more valuable than personal preference.