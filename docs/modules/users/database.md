# Users Database Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the database design for the Users module.

The Users module stores platform users and their personal information.

It does not define yacht responsibilities or permissions.

---

## Database Overview

The Users module is centered around the `users` table.

A user represents a person who can access YachtOS.

Operational responsibilities such as Owner, Captain, Crew or Engineer are managed through yacht memberships, not within the Users module.

---

## Tables

### users

Represents a platform user.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| first_name | User's first name |
| last_name | User's last name |
| email | Unique email address |
| phone | Contact phone number |
| avatar | Profile image |
| language | Preferred language |
| timezone | Preferred timezone |
| status | Active, Inactive or Archived |
| last_login_at | Last successful login |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

## Relationships

User

└── Yacht Memberships

---

## Design Rules

- Every user must have a unique email address.
- A user represents a platform account.
- Yacht roles must never be stored directly on the user record.
- Yacht assignments are managed through the Yacht Membership relationship.
- A user may belong to multiple yachts over time.
- Historical user records must always be preserved.
- Users linked to historical operational data must never be permanently deleted.
- Guests are not platform users unless they require access to YachtOS.

---

## Future Compatibility

The current design supports future expansion for:

- Single Sign-On (SSO)
- Multi-Factor Authentication (MFA)
- External Identity Providers
- API Tokens
- Multiple Authentication Methods

These features can be added without changing the core user data model.