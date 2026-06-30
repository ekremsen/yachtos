# Users API Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the REST API design for the Users module.

The Users API is responsible for managing platform users.

Authentication, authorization and yacht assignments are handled by their respective modules.

---

## Responsibilities

The Users API is responsible for:

- Creating users
- Updating user information
- Listing users
- Viewing user details
- Archiving users

The Users API is not responsible for:

- Authentication
- Authorization
- Yacht memberships
- Role permissions

---

## API Principles

- REST API will be used.
- Every request must be authenticated.
- Every request must be authorized.
- Every request must be scoped to the authenticated tenant.
- Controllers must not contain business logic.

---

## Endpoints

### List Users

GET /api/users

Returns all users belonging to the authenticated tenant.

---

### Get User

GET /api/users/{id}

Returns detailed information for a specific user.

---

### Create User

POST /api/users

Creates a new platform user.

---

### Update User

PUT /api/users/{id}

Updates user information.

---

### Archive User

PATCH /api/users/{id}/archive

Archives a user while preserving historical data.

---

## Authorization Rules

- Users may only access users belonging to their tenant.
- User creation requires the appropriate permission.
- User updates require the appropriate permission.
- Archived users cannot perform authenticated operations.

---

## Validation Rules

The API must validate:

- Authentication
- Tenant ownership
- User permissions
- Request payload
- User status

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