# Crew UI Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the user interface requirements for the Crew module.

The Crew module enables captains and administrators to manage crew assignments, certifications and operational information.

---

## Design Principles

- Simple
- Mobile-first
- Fast
- Operationally focused
- Minimal data entry
- Easy navigation

---

## Crew List

The crew list should display:

- Full Name
- Position
- Assigned Yacht
- Employment Type
- Assignment Status
- Certification Status

Available actions:

- View
- Edit
- End Assignment

The list should support:

- Search
- Filtering
- Sorting
- Pagination

---

## Crew Detail

The crew detail page should display:

### Personal Information

- Full Name
- Contact Information

### Assignment Information

- Assigned Yacht
- Position
- Employment Type
- Assignment Status
- Start Date
- End Date

### Certifications

- Certificate Name
- Expiration Date
- Status

---

## Assign Crew

Required fields:

- User
- Yacht
- Position
- Start Date

Optional fields:

- Employment Type
- Notes

---

## Edit Assignment

Users may update:

- Position
- Employment Type
- Notes

Historical assignment information must remain unchanged.

---

## Certification Management

Users should be able to:

- Add certifications
- Update certifications
- View expiration dates

Expired certifications should be clearly highlighted.

---

## UX Rules

- Crew assignment should take less than one minute.
- Use searchable dropdowns for selecting users and yachts.
- Highlight expired certifications.
- Prevent duplicate active assignments.
- Minimize required input.

---

## Mobile Requirements

Captains should be able to:

- View crew
- Assign crew
- End assignments
- Check certifications

using a mobile device.