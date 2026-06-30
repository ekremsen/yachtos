# Crew Database Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the database design for the Crew module.

The Crew module manages operational assignments between users and yachts, including assignment history and certification tracking.

---

## Database Overview

The Crew module is centered around two core tables:

- crew_assignments
- crew_certifications

---

## Tables

### crew_assignments

Represents a crew assignment on a yacht.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| tenant_id | Owner tenant |
| yacht_id | Assigned yacht |
| user_id | Assigned user |
| position | Crew position |
| employment_type | Full Time, Seasonal, Contract |
| start_date | Assignment start date |
| end_date | Assignment end date |
| status | Active, Inactive |
| notes | Internal notes |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

### crew_certifications

Represents certifications held by crew members.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| crew_assignment_id | Related crew assignment |
| certificate_name | Certificate title |
| certificate_number | Certificate number |
| issue_date | Issue date |
| expiry_date | Expiration date |
| issuing_authority | Issuing organization |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

## Relationships

Crew Assignment

├── User

├── Yacht

└── Crew Certifications

---

## Design Rules

- Every crew assignment belongs to exactly one tenant.
- Every crew assignment belongs to exactly one yacht.
- Every crew assignment belongs to exactly one user.
- Historical assignments must always be preserved.
- Active assignments must not overlap for the same user on the same yacht.
- Certifications are linked to crew assignments.
- Hard delete is prohibited for historical operational data.

---

## Future Compatibility

The current design supports future expansion for:

- Payroll integration
- HR management
- Leave tracking
- Performance evaluations
- Training management
- Medical certificate tracking