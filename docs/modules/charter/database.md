# Charter Database Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the database design for the Charter module.

The Charter module stores commercial charter bookings, guest information and charter history.

---

## Database Overview

The Charter module is centered around three core tables:

- charter_bookings
- charter_guests
- charter_payments

---

## Tables

### charter_bookings

Represents a charter reservation.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| tenant_id | Owner tenant |
| yacht_id | Related yacht |
| booking_reference | Booking reference number |
| customer_name | Primary customer |
| start_date | Charter start date |
| end_date | Charter end date |
| guest_count | Number of guests |
| total_price | Charter price |
| currency | Currency code |
| status | Draft, Pending, Confirmed, In Progress, Completed or Cancelled |
| notes | Internal notes |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

### charter_guests

Represents guests participating in a charter.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| booking_id | Related charter booking |
| first_name | First name |
| last_name | Last name |
| nationality | Nationality |
| passport_number | Passport number |
| date_of_birth | Date of birth |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

### charter_payments

Represents payments related to a charter booking.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| booking_id | Related charter booking |
| amount | Payment amount |
| currency | Currency code |
| payment_method | Payment method |
| payment_date | Payment date |
| status | Pending, Paid or Refunded |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

## Relationships

Charter Booking

├── Yacht

├── Charter Guests

└── Charter Payments

---

## Design Rules

- Every charter booking belongs to exactly one tenant.
- Every charter booking belongs to exactly one yacht.
- Charter bookings must not overlap for the same yacht.
- Every guest belongs to exactly one charter booking.
- Every payment belongs to exactly one charter booking.
- Charter history must always be preserved.
- Hard delete is prohibited for completed charter records.

---

## Future Compatibility

The current design supports future expansion for:

- Availability calendars
- Dynamic pricing
- Online reservations
- Customer portal
- Contract management
- Digital signatures
- Payment gateway integrations
- Travel agency integrations
- CRM integrations