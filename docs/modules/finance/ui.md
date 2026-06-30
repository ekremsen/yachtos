# Finance UI Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the user interface requirements for the Finance module.

The Finance module should enable captains and yacht owners to record, monitor and review operational expenses in a simple and transparent way.

---

## Design Principles

- Mobile-first
- Operationally focused
- Fast expense entry
- Clear financial visibility
- Consistent user experience
- Minimal data entry

---

## Expense List

The expense list should display:

- Title
- Yacht
- Category
- Amount
- Currency
- Transaction Date
- Status

Available actions:

- View
- Edit
- Approve
- Reject

The list should support:

- Search
- Filtering
- Sorting
- Pagination

Pending approval expenses should be visually highlighted.

---

## Expense Detail

The expense detail page should display:

### General Information

- Title
- Description
- Expense Category
- Yacht
- Status

### Financial Information

- Amount
- Currency
- Transaction Date

### Approval Information

- Created By
- Approved By
- Approval Date

### Attachments

- Receipts
- Invoices
- Supporting Documents

### Notes

- Internal Notes
- Approval Notes

---

## Create Expense

Required fields:

- Yacht
- Expense Category
- Title
- Amount
- Transaction Date

Optional fields:

- Description
- Currency
- Notes
- Attachments

---

## Edit Expense

Users may update:

- Title
- Description
- Expense Category
- Amount
- Transaction Date
- Notes

Approved expenses cannot be edited except by authorized administrators.

---

## Attachment Management

Users should be able to:

- Upload attachments
- View attachments
- Download attachments
- Delete attachments (if permitted)

Supported attachment types include:

- Images
- PDF documents
- Office documents

---

## UX Rules

- Expense creation should take less than one minute.
- Currency should be selected automatically from tenant settings by default.
- Frequently used expense categories should appear first.
- Pending approval expenses should be clearly distinguishable.
- Financial totals should be easy to read.

---

## Mobile Requirements

Captains should be able to:

- Record expenses
- Upload receipts
- View expense history
- Approve expenses (if authorized)

using a mobile device without requiring a desktop computer.