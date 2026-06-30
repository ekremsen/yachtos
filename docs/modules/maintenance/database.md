# Maintenance Database Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the database design for the Maintenance module.

The Maintenance module stores all maintenance activities performed throughout a yacht's operational lifecycle.

---

## Database Overview

The Maintenance module is centered around two core tables:

- maintenances
- maintenance_attachments

---

## Tables

### maintenances

Represents a maintenance activity.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| tenant_id | Owner tenant |
| yacht_id | Related yacht |
| assigned_user_id | Responsible user |
| title | Maintenance title |
| description | Detailed description |
| type | Preventive, Corrective or Inspection |
| priority | Low, Medium, High or Critical |
| status | Planned, In Progress, Completed or Cancelled |
| scheduled_at | Planned maintenance date |
| completed_at | Completion date |
| estimated_cost | Estimated maintenance cost |
| actual_cost | Actual maintenance cost |
| notes | Internal notes |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

### maintenance_attachments

Represents files attached to a maintenance record.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| maintenance_id | Related maintenance |
| file_name | Original file name |
| file_path | Storage location |
| file_type | MIME type |
| uploaded_by | User who uploaded the file |
| created_at | Upload timestamp |

---

## Relationships

Maintenance

├── Yacht

├── Assigned User

└── Maintenance Attachments

---

## Design Rules

- Every maintenance record belongs to exactly one tenant.
- Every maintenance record belongs to exactly one yacht.
- Every maintenance record may be assigned to one responsible user.
- Attachments belong to a single maintenance record.
- Historical maintenance records must always be preserved.
- Completed maintenance records must never be permanently deleted.
- Hard delete is prohibited for maintenance records linked to yacht history.

---

## Future Compatibility

The current design supports future expansion for:

- Recurring maintenance schedules
- Maintenance templates
- Spare parts integration
- Vendor management
- Purchase orders
- Predictive maintenance
- IoT integrations
- Warranty tracking