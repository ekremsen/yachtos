# Maintenance API Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the REST API design for the Maintenance module.

The Maintenance API is responsible for managing maintenance activities throughout the operational lifecycle of a yacht.

---

## Responsibilities

The Maintenance API is responsible for:

- Creating maintenance records
- Updating maintenance information
- Listing maintenance records
- Viewing maintenance details
- Managing maintenance status
- Managing maintenance attachments

The Maintenance API is **not** responsible for:

- Inventory management
- Financial management
- Yacht management
- User management

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

### List Maintenance Records

GET /api/maintenances

Returns all maintenance records belonging to the authenticated tenant.

---

### Get Maintenance Record

GET /api/maintenances/{id}

Returns detailed information for a maintenance record.

---

### Create Maintenance Record

POST /api/maintenances

Creates a new maintenance record.

---

### Update Maintenance Record

PUT /api/maintenances/{id}

Updates maintenance information.

---

### Complete Maintenance

PATCH /api/maintenances/{id}/complete

Marks a maintenance record as completed.

---

### Cancel Maintenance

PATCH /api/maintenances/{id}/cancel

Cancels a planned maintenance.

---

### List Attachments

GET /api/maintenances/{id}/attachments

Returns all attachments for a maintenance record.

---

### Upload Attachment

POST /api/maintenances/{id}/attachments

Uploads a new attachment.

---

### Delete Attachment

DELETE /api/maintenances/{id}/attachments/{attachmentId}

Deletes an attachment if allowed by business rules.

---

## Authorization Rules

- Users may only access maintenance records belonging to their tenant.
- Creating maintenance requires the appropriate permission.
- Updating maintenance requires the appropriate permission.
- Completing or cancelling maintenance requires the appropriate permission.

---

## Validation Rules

The API must validate:

- Authentication
- Tenant ownership
- Yacht existence
- Assigned user existence
- Maintenance status transitions
- Scheduled and completion dates
- Uploaded file types and sizes

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