# Yacht API Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the REST API design for the Yacht module.

The Yacht module is responsible for managing yachts throughout their operational lifecycle.

---

## Responsibilities

The Yacht API is responsible for:

- Creating yachts
- Updating yacht information
- Listing yachts
- Viewing yacht details
- Archiving yachts

The Yacht API is **not** responsible for:

- Crew management
- Maintenance
- Inventory
- Guest management
- Documents
- Financial operations

These responsibilities belong to their respective modules.

---

## API Principles

- REST API will be used.
- Every request must be authenticated.
- Every request must be authorized.
- Every request must be scoped to the authenticated tenant.
- Controllers must not contain business logic.

---

## Endpoints

### List Yachts

```http
GET /api/yachts
```

Returns all yachts belonging to the authenticated tenant.

---

### Get Yacht

```http
GET /api/yachts/{id}
```

Returns detailed information for a specific yacht.

---

### Create Yacht

```http
POST /api/yachts
```

Creates a new yacht.

---

### Update Yacht

```http
PUT /api/yachts/{id}
```

Updates yacht information.

---

### Archive Yacht

```http
PATCH /api/yachts/{id}/archive
```

Archives a yacht while preserving historical data.

---

## Authorization Rules

- Users may only access yachts belonging to their tenant.
- Yacht creation requires the appropriate permission.
- Yacht updates require the appropriate permission.
- Archived yachts cannot be modified.

---

## Validation Rules

The API must validate:

- Authentication
- Tenant ownership
- User permissions
- Request payload
- Yacht status

---

## Error Responses

| Status Code | Description |
|--------------|-------------|
| 401 | Unauthenticated |
| 403 | Forbidden |
| 404 | Resource not found |
| 409 | Business rule conflict |
| 422 | Validation failed |
| 500 | Internal server error |

---

## Related Documents

- business-rules.md
- database.md
- ui.md
- acceptance-criteria.md