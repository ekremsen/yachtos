# Users UI Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the user interface requirements for the Users module.

The Users module provides a simple and consistent interface for managing platform users.

---

## Design Principles

- Simple
- Fast
- Mobile-first
- Consistent
- Accessible
- Easy to learn

---

## User List

The user list should display:

- Full Name
- Email Address
- Phone Number
- Status
- Last Login

Available actions:

- View
- Edit
- Archive

The list should support:

- Search
- Sorting
- Filtering
- Pagination

---

## User Detail

The user detail page should display:

### Personal Information

- First Name
- Last Name
- Email
- Phone
- Profile Photo

### Preferences

- Language
- Timezone

### Account Information

- Status
- Last Login
- Created Date

### Yacht Memberships

Display all yacht assignments associated with the user.

This information is read from the Yacht Membership module.

---

## Create User

Required fields:

- First Name
- Last Name
- Email Address

Optional fields:

- Phone Number
- Language
- Timezone
- Profile Photo

The user should be created without assigning any yacht responsibilities.

---

## Edit User

Users may update:

- Personal information
- Contact information
- Preferences

Yacht assignments cannot be edited from this screen.

---

## UX Rules

- User creation should take less than one minute.
- Keep required fields to a minimum.
- Group related information together.
- Minimize typing whenever possible.
- Use validation messages that clearly explain the problem.
- The interface must remain fully usable on tablets and mobile devices.

---

## Mobile Requirements

Users must be manageable from mobile devices.

Administrators should be able to:

- View users
- Create users
- Edit users
- Archive users

without requiring a desktop computer.