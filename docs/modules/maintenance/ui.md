# Maintenance UI Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the user interface requirements for the Maintenance module.

The Maintenance module should enable captains and yacht operators to easily plan, monitor and complete maintenance activities.

---

## Design Principles

- Mobile-first
- Operationally focused
- Fast data entry
- Clear maintenance status
- Minimal navigation
- Consistent user experience

---

## Maintenance List

The maintenance list should display:

- Title
- Yacht
- Maintenance Type
- Priority
- Status
- Scheduled Date
- Assigned User

Available actions:

- View
- Edit
- Complete
- Cancel

The list should support:

- Search
- Filtering
- Sorting
- Pagination

---

## Maintenance Detail

The maintenance detail page should display:

### General Information

- Title
- Description
- Maintenance Type
- Priority
- Status

### Scheduling

- Scheduled Date
- Completion Date

### Assignment

- Responsible User
- Related Yacht

### Financial Information

- Estimated Cost
- Actual Cost

### Attachments

- Photos
- Invoices
- Service Reports
- Other Files

### Notes

- Internal Notes
- Completion Notes

---

## Create Maintenance

Required fields:

- Yacht
- Title
- Maintenance Type
- Scheduled Date

Optional fields:

- Description
- Priority
- Responsible User
- Estimated Cost
- Notes

---

## Edit Maintenance

Users may update:

- General information
- Schedule
- Priority
- Assigned user
- Estimated cost
- Notes

Completed maintenance records cannot be edited except by authorized administrators.

---

## Attachment Management

Users should be able to:

- Upload files
- View attachments
- Download attachments
- Remove attachments (if permitted)

Supported attachment types include:

- Images
- PDF documents
- Office documents

---

## UX Rules

- Maintenance creation should take less than one minute.
- Status should always be clearly visible.
- Overdue maintenance should be visually highlighted.
- Critical maintenance should be distinguishable from standard maintenance.
- Frequently used actions should require the fewest possible clicks.

---

## Mobile Requirements

Captains should be able to:

- View maintenance records
- Create maintenance
- Complete maintenance
- Upload photos
- Add notes

using a mobile device without requiring a desktop computer.