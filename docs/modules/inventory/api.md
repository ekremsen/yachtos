# Inventory API Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the REST API design for the Inventory module.

The Inventory API is responsible for managing onboard inventory items and stock movements.

---

## Responsibilities

The Inventory API is responsible for:

- Creating inventory items
- Updating inventory items
- Listing inventory items
- Viewing inventory details
- Recording stock movements
- Managing inventory categories

The Inventory API is **not** responsible for:

- Purchasing
- Supplier management
- Financial accounting
- Maintenance

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

### List Inventory Items

GET /api/inventory

Returns all inventory items belonging to the authenticated tenant.

---

### Get Inventory Item

GET /api/inventory/{id}

Returns detailed information for an inventory item.

---

### Create Inventory Item

POST /api/inventory

Creates a new inventory item.

---

### Update Inventory Item

PUT /api/inventory/{id}

Updates inventory information.

---

### Archive Inventory Item

PATCH /api/inventory/{id}/archive

Archives an inventory item while preserving historical stock movements.

---

### Record Stock Movement

POST /api/inventory/{id}/movements

Creates a stock movement.

---

### List Stock Movements

GET /api/inventory/{id}/movements

Returns all stock movements for an inventory item.

---

### List Categories

GET /api/inventory/categories

Returns all inventory categories.

---

### Create Category

POST /api/inventory/categories

Creates a new inventory category.

---

### Update Category

PUT /api/inventory/categories/{id}

Updates an inventory category.

---

## Authorization Rules

- Users may only access inventory belonging to their tenant.
- Inventory creation requires the appropriate permission.
- Inventory updates require the appropriate permission.
- Stock movements require the appropriate permission.
- Archived inventory items cannot receive new stock movements.

---

## Validation Rules

The API must validate:

- Authentication
- Tenant ownership
- Yacht existence
- Category existence
- Stock availability
- User permissions
- Stock movement consistency

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