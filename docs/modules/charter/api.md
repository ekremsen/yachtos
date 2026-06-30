# Charter API Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the REST API design for the Charter module.

The Charter API is responsible for managing charter bookings, guests and charter payments.

---

## Responsibilities

The Charter API is responsible for:

- Creating charter bookings
- Updating charter bookings
- Listing charter bookings
- Viewing booking details
- Managing guests
- Managing charter payments

The Charter API is **not** responsible for:

- Yacht management
- Crew management
- Maintenance management
- Inventory management

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

### List Charter Bookings

GET /api/charter-bookings

Returns all charter bookings belonging to the authenticated tenant.

---

### Get Charter Booking

GET /api/charter-bookings/{id}

Returns detailed information for a charter booking.

---

### Create Charter Booking

POST /api/charter-bookings

Creates a new charter booking.

---

### Update Charter Booking

PUT /api/charter-bookings/{id}

Updates booking information.

---

### Confirm Charter Booking

PATCH /api/charter-bookings/{id}/confirm

Confirms a pending charter booking.

---

### Cancel Charter Booking

PATCH /api/charter-bookings/{id}/cancel

Cancels a charter booking.

---

### List Guests

GET /api/charter-bookings/{id}/guests

Returns all guests assigned to a charter booking.

---

### Add Guest

POST /api/charter-bookings/{id}/guests

Adds a guest to a charter booking.

---

### Update Guest

PUT /api/charter-bookings/{id}/guests/{guestId}

Updates guest information.

---

### Remove Guest

DELETE /api/charter-bookings/{id}/guests/{guestId}

Removes a guest if permitted by business rules.

---

### List Payments

GET /api/charter-bookings/{id}/payments

Returns all payments for a charter booking.

---

### Record Payment

POST /api/charter-bookings/{id}/payments

Records a payment for a charter booking.

---

## Authorization Rules

- Users may only access charter bookings belonging to their tenant.
- Booking creation requires the appropriate permission.
- Booking updates require the appropriate permission.
- Booking confirmation requires the appropriate permission.
- Completed bookings cannot be modified except by authorized administrators.

---

## Validation Rules

The API must validate:

- Authentication
- Tenant ownership
- Yacht availability
- Booking dates
- Guest information
- Payment information
- User permissions

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