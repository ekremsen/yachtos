# Crew API Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the REST API design for the Crew module.

The Crew API is responsible for managing crew assignments and certifications.

---

## Responsibilities

The Crew API is responsible for:

- Assigning users to yachts
- Updating crew assignments
- Listing crew members
- Viewing crew details
- Managing crew certifications
- Ending crew assignments

The Crew API is **not** responsible for:

- User profile management
- Authentication
- Yacht management
- Payroll
- Leave management

---

## API Principles

- REST API will be used.
- Every request must be authenticated.
- Every request must be authorized.
- Every request must be scoped to the authenticated tenant.
- Controllers must not contain business logic.

---

## Endpoints

### List Crew Assignments

GET /api/crew

Returns all crew assignments belonging to the authenticated tenant.

---

### Get Crew Assignment

GET /api/crew/{id}

Returns detailed information for a crew assignment.

---

### Create Crew Assignment

POST /api/crew

Assigns a user to a yacht.

---

### Update Crew Assignment

PUT /api/crew/{id}

Updates assignment information.

---

### End Crew Assignment

PATCH /api/crew/{id}/end

Ends an active crew assignment while preserving history.

---

### List Certifications

GET /api/crew/{id}/certifications

Returns certifications for a crew assignment.

---

### Add Certification

POST /api/crew/{id}/certifications

Adds a certification.

---

### Update Certification

PUT /api/crew/{id}/certifications/{certificationId}

Updates certification information.

---

## Authorization Rules

- Users may only access crew assignments belonging to their tenant.
- Crew creation requires the appropriate permission.
- Crew updates require the appropriate permission.
- Assignment history cannot be modified after completion.

---

## Validation Rules

The API must validate:

- Authentication
- Tenant ownership
- Yacht existence
- User existence
- Assignment conflicts
- Certification dates

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