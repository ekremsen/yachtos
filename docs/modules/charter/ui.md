# Charter UI Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the user interface requirements for the Charter module.

The Charter module enables charter operators to manage bookings, guests and payments while integrating seamlessly with yacht operations.

---

## Design Principles

- Mobile-first
- Commercially focused
- Simple booking workflow
- Fast guest management
- Clear booking status
- Consistent user experience

---

## Charter Booking List

The booking list should display:

- Booking Reference
- Yacht
- Primary Customer
- Charter Dates
- Guest Count
- Booking Status
- Total Price

Available actions:

- View
- Edit
- Confirm
- Cancel

The list should support:

- Search
- Filtering
- Sorting
- Pagination

Upcoming charters should be visually highlighted.

---

## Charter Booking Detail

The booking detail page should display:

### Booking Information

- Booking Reference
- Yacht
- Primary Customer
- Status

### Schedule

- Start Date
- End Date
- Duration

### Guests

- Guest List
- Nationality
- Passport Information

### Payments

- Total Price
- Paid Amount
- Outstanding Balance
- Payment History

### Notes

- Internal Notes
- Customer Notes

---

## Create Charter Booking

Required fields:

- Yacht
- Primary Customer
- Start Date
- End Date

Optional fields:

- Guest Count
- Notes

---

## Edit Charter Booking

Users may update:

- Customer Information
- Charter Dates
- Notes

Confirmed bookings may only be edited by authorized users.

---

## Guest Management

Users should be able to:

- Add guests
- Edit guest information
- Remove guests
- View guest list

Guest information should be easy to access before departure.

---

## Payment Management

Users should be able to:

- Record payments
- View payment history
- Monitor outstanding balances

---

## UX Rules

- Charter booking creation should take less than two minutes.
- Booking status should always be clearly visible.
- Booking conflicts should be detected before submission.
- Payment summary should always be visible.
- Upcoming charters should be prioritized in the interface.

---

## Mobile Requirements

Charter managers should be able to:

- View bookings
- Create bookings
- Manage guests
- Record payments

using a mobile device without requiring a desktop computer.