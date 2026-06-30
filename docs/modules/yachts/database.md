# Yacht Database Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the database design for the Yacht module.

The Yacht module represents the central operational asset of YachtOS and serves as the foundation for all operational data within the platform.

---

## Database Overview

The Yacht module is centered around the `yachts` table.

Every operational module in YachtOS is associated with a yacht, making it the primary business entity of the platform.

---

## Tables

### yachts

Represents a yacht managed within YachtOS.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| tenant_id | Owner tenant |
| name | Yacht name |
| imo_number | IMO number (if applicable) |
| registration_number | Registration number |
| hull_number | Hull identification number |
| flag_country | Flag state |
| home_port | Home marina or port |
| yacht_type | Motor Yacht, Sailing Yacht, Catamaran, etc. |
| length | Overall length |
| beam | Maximum width |
| draft | Maximum draft |
| gross_tonnage | Gross tonnage |
| build_year | Construction year |
| manufacturer | Builder |
| model | Yacht model |
| status | Active, Inactive, Archived |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

## Relationships

```text
Tenant
└── Yacht
    ├── Yacht Members
    ├── Maintenance Records
    ├── Inventory Items
    ├── Guest Preferences
    ├── Documents
    ├── Tasks
    └── Expenses
```

---

## Design Rules

- Every yacht must belong to exactly one tenant.
- Every yacht must have a valid `tenant_id`.
- Every operational record must reference a valid `yacht_id`.
- Owner, Captain and Crew relationships must never be stored directly on the `yachts` table.
- User assignments are managed through the `yacht_members` table.
- Physical identifiers (`imo_number`, `registration_number`, `hull_number`) belong to the yacht record.
- Yacht records must never be permanently deleted.
- Yacht lifecycle is managed through the `status` field.
- Historical operational data must always remain available for auditing.

---

## Future Compatibility

The current design supports future expansion without structural changes for:

- Private yacht owners
- Charter companies
- Yacht management companies
- Fleet operators

Additional business modules will extend the yacht through relationships rather than modifying the core yacht structure.