# Finance API Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the REST API design for the Finance module.

The Finance API is responsible for managing operational expenses, budgets and financial records.

---

## Responsibilities

The Finance API is responsible for:

- Creating expenses
- Updating expenses
- Listing expenses
- Viewing expense details
- Managing expense approvals
- Managing expense categories
- Managing expense attachments

The Finance API is **not** responsible for:

- Payroll
- Accounting
- Banking integrations
- Supplier management

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

### List Expenses

GET /api/expenses

Returns all expenses belonging to the authenticated tenant.

---

### Get Expense

GET /api/expenses/{id}

Returns detailed information for an expense.

---

### Create Expense

POST /api/expenses

Creates a new expense.

---

### Update Expense

PUT /api/expenses/{id}

Updates an editable expense.

---

### Approve Expense

PATCH /api/expenses/{id}/approve

Approves an expense.

---

### Reject Expense

PATCH /api/expenses/{id}/reject

Rejects an expense.

---

### List Expense Categories

GET /api/expense-categories

Returns all expense categories.

---

### Create Expense Category

POST /api/expense-categories

Creates a new expense category.

---

### Update Expense Category

PUT /api/expense-categories/{id}

Updates an expense category.

---

### List Expense Attachments

GET /api/expenses/{id}/attachments

Returns all attachments for an expense.

---

### Upload Expense Attachment

POST /api/expenses/{id}/attachments

Uploads a new attachment.

---

### Delete Expense Attachment

DELETE /api/expenses/{id}/attachments/{attachmentId}

Deletes an attachment if permitted by business rules.

---

## Authorization Rules

- Users may only access financial records belonging to their tenant.
- Expense creation requires the appropriate permission.
- Expense updates require the appropriate permission.
- Expense approval requires the appropriate approval permission.
- Approved expenses cannot be modified except by authorized administrators.

---

## Validation Rules

The API must validate:

- Authentication
- Tenant ownership
- Yacht existence
- Expense category existence
- Expense amount
- Transaction date
- User permissions
- Approval workflow

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