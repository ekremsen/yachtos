# Tenant API Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the REST API design for the Tenant module.

The Tenant module is responsible for organization-level information and settings.

It does not manage yachts, users or other business entities.

---

## Responsibilities

The Tenant API is responsible for:

- Organization information
- Organization settings
- Tenant configuration

The following resources belong to their own modules:

- Yachts
- Users
- Crew
- Maintenance
- Inventory
- Finance
- Charter

---

## API Principles

- REST API will be used for the MVP.
- Every request must be authenticated.
- Every request must be authorized.
- Every request must be scoped to the authenticated tenant.
- Business logic must never exist inside controllers.

---

## Endpoints

### Get Current Tenant

```http
GET /api/tenant
```

Returns the authenticated user's current tenant.

---

### Update Current Tenant

```http
PATCH /api/tenant
```

Updates editable tenant information.

#### Editable Fields

- name
- country
- timezone
- currency

---

### Get Tenant Settings

```http
GET /api/tenant/settings
```

Returns tenant-specific application settings.

---

### Update Tenant Settings

```http
PATCH /api/tenant/settings
```

Updates tenant-specific application settings.

---

## Authorization Rules

Only authenticated users belonging to the current tenant may access tenant resources.

Only users with sufficient permissions may update tenant information or settings.

---

## Validation Rules

The API must validate:

- Authentication
- Tenant membership
- User permissions
- Request payload

Validation failures must return standard REST error responses.

---

## Error Responses

| Status Code | Description |
|--------------|-------------|
| 401 | Unauthenticated |
| 403 | Forbidden |
| 404 | Resource not found |
| 422 | Validation failed |
| 500 | Internal server error |

---

## Related Documents

- business-rules.md
- database.md
- ui.md