# ADR-0003 - REST Endpoint Conventions

**Status:** Accepted

**Date:** 29 June 2026

---

## Context

YachtOS will expose REST APIs for web and future mobile clients.

A consistent endpoint convention is required to keep the API predictable, easy to document and easy to maintain.

---

## Decision

YachtOS will use resource-oriented REST endpoint conventions.

Endpoints represent resources, not actions.

---

## HTTP Method Rules

| Action | HTTP Method | Example |
|---------|-------------|---------|
| List | GET | GET /api/yachts |
| Detail | GET | GET /api/yachts/{id} |
| Create | POST | POST /api/yachts |
| Update | PUT | PUT /api/yachts/{id} |
| State Change | PATCH | PATCH /api/yachts/{id}/archive |
| Delete | DELETE* | DELETE /api/yachts/{id} |

---

## PUT Rule

PUT is used for full resource updates.

Example:

    PUT /api/yachts/{id}

The client sends the complete representation of the resource.

---

## PATCH Rule

PATCH is used only for state transition actions or partial updates.

Examples:

    PATCH /api/yachts/{id}/archive
    PATCH /api/tasks/{id}/complete
    PATCH /api/users/{id}/activate

PATCH should never replace PUT for normal edit operations.

---

## DELETE Rule

DELETE may be used where permanent deletion is considered a valid business operation.

The decision to expose DELETE endpoints is made on a module-by-module basis.

Business-critical modules may prohibit DELETE operations to preserve historical and audit data.

The use of DELETE must be explicitly documented within each module.

---

## Endpoint Naming Rules

Endpoints must follow these conventions:

- Use lowercase letters.
- Use kebab-case where necessary.
- Use plural resource names.
- Never use verbs in endpoint names.

Good examples:

    GET /api/yachts
    GET /api/maintenance-tasks
    POST /api/guest-preferences

Bad examples:

    POST /api/createYacht
    POST /api/update_yacht
    GET /api/YachtList

---

## Controller Responsibilities

Controllers are responsible for:

- Validating requests
- Calling application services
- Returning responses

Controllers must never contain business logic.

---

## Response Standards

- Use appropriate HTTP status codes.
- Return consistent response structures.
- Do not expose internal implementation details.
- Validation errors should return HTTP 422.
- Authorization failures should return HTTP 403.
- Authentication failures should return HTTP 401.

---

## Consequences

### Positive

- Predictable API design
- Easier frontend integration
- Easier API documentation
- Better long-term maintainability
- Consistent developer experience

### Negative

- Requires discipline across all modules
- Some state changes require dedicated PATCH endpoints

---

## Final Decision

YachtOS REST APIs will follow resource-oriented endpoint conventions.

PUT will be used for full resource updates.

PATCH will be used only for state transitions and partial updates.

These conventions are mandatory for every API module in YachtOS.