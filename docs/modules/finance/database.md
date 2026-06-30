# Finance Database Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the database design for the Finance module.

The Finance module stores operational financial transactions, budgets and expense history for yachts.

---

## Database Overview

The Finance module is centered around three core tables:

- expenses
- expense_categories
- expense_attachments

---

## Tables

### expenses

Represents an operational expense.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| tenant_id | Owner tenant |
| yacht_id | Related yacht |
| category_id | Expense category |
| created_by | User who created the expense |
| approved_by | User who approved the expense |
| title | Expense title |
| description | Expense description |
| amount | Expense amount |
| currency | Currency code |
| transaction_date | Expense date |
| status | Draft, Pending Approval, Approved or Rejected |
| notes | Internal notes |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

### expense_categories

Represents expense categories.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| tenant_id | Owner tenant |
| name | Category name |
| description | Category description |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

### expense_attachments

Represents files attached to an expense.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| expense_id | Related expense |
| file_name | Original file name |
| file_path | Storage path |
| file_type | MIME type |
| uploaded_by | User who uploaded the attachment |
| created_at | Upload timestamp |

---

## Relationships

Expense Category

└── Expenses

├── Yacht

├── Created By (User)

├── Approved By (User)

└── Expense Attachments

---

## Design Rules

- Every expense belongs to exactly one tenant.
- Every expense belongs to exactly one yacht.
- Every expense belongs to exactly one expense category.
- Every expense has one creator.
- Expense approval is optional and depends on tenant policies.
- Attachments belong to a single expense.
- Financial history must never be permanently deleted.
- Approved expenses become read-only.
- Hard delete is prohibited for financial records.

---

## Future Compatibility

The current design supports future expansion for:

- Budget management
- Purchase requests
- Purchase orders
- Vendor management
- Multi-currency support
- Tax reporting
- Accounting integrations
- Bank integrations
- Financial dashboards