# Inventory UI Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Product Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the user interface requirements for the Inventory module.

The Inventory module should allow captains and crew members to efficiently manage onboard inventory with minimal effort.

---

## Design Principles

- Mobile-first
- Fast inventory operations
- Minimal data entry
- Clear stock visibility
- Consistent user experience
- Easy navigation

---

## Inventory List

The inventory list should display:

- Item Name
- Category
- Current Quantity
- Unit
- Storage Location
- Stock Status

Available actions:

- View
- Edit
- Record Stock Movement
- Archive

The list should support:

- Search
- Filtering
- Sorting
- Pagination

Low stock items should be visually highlighted.

---

## Inventory Detail

The inventory detail page should display:

### General Information

- Item Name
- Category
- Unit
- Storage Location
- Current Quantity
- Minimum Quantity
- Status

### Stock Summary

- Current Stock
- Total Stock In
- Total Stock Out
- Last Movement Date

### Stock Movement History

Display a chronological list of:

- Movement Type
- Quantity
- Reason
- Performed By
- Date

---

## Create Inventory Item

Required fields:

- Item Name
- Category
- Unit

Optional fields:

- Storage Location
- Minimum Quantity
- Notes

---

## Edit Inventory Item

Users may update:

- Item Name
- Category
- Storage Location
- Minimum Quantity
- Notes

Current stock quantity cannot be edited directly.

Stock changes must be performed through stock movements.

---

## Record Stock Movement

Required fields:

- Movement Type
- Quantity
- Reason

Optional fields:

- Notes

The system should immediately update the available stock after a successful movement.

---

## UX Rules

- Inventory item creation should take less than one minute.
- Stock status should always be visible.
- Low stock items should be highlighted.
- Stock movements should require the minimum number of clicks.
- Prevent negative stock before submission.

---

## Mobile Requirements

Captains and authorized crew members should be able to:

- View inventory
- Search inventory
- Record stock movements
- Check low stock items

using a mobile device without requiring a desktop computer.