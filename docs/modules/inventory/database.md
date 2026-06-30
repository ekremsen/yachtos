# Inventory Database Design

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the database design for the Inventory module.

The Inventory module stores all onboard inventory items, stock movements and inventory history.

---

## Database Overview

The Inventory module is centered around three core tables:

- inventory_items
- inventory_movements
- inventory_categories

---

## Tables

### inventory_items

Represents an inventory item stored on a yacht.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| tenant_id | Owner tenant |
| yacht_id | Related yacht |
| category_id | Inventory category |
| name | Item name |
| sku | Internal stock code |
| unit | Unit of measurement |
| quantity | Current quantity |
| minimum_quantity | Minimum stock level |
| storage_location | Storage location |
| status | Active or Archived |
| notes | Internal notes |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

### inventory_movements

Represents every stock movement.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| tenant_id | Owner tenant |
| inventory_item_id | Related inventory item |
| user_id | User performing the movement |
| movement_type | In, Out or Adjustment |
| quantity | Movement quantity |
| reason | Reason for movement |
| created_at | Movement timestamp |

---

### inventory_categories

Represents inventory categories.

#### Columns

| Column | Description |
|---------|-------------|
| id | Unique identifier |
| tenant_id | Owner tenant |
| name | Category name |
| created_at | Creation timestamp |
| updated_at | Last update timestamp |

---

## Relationships

Inventory Category

└── Inventory Items

&nbsp;&nbsp;&nbsp;&nbsp;└── Inventory Movements

---

## Design Rules

- Every inventory item belongs to exactly one tenant.
- Every inventory item belongs to exactly one yacht.
- Every inventory item belongs to exactly one category.
- Every inventory movement belongs to exactly one inventory item.
- Every inventory movement belongs to exactly one user.
- Inventory quantity is calculated from stock movements.
- Stock movement history must never be deleted.
- Inventory items with movement history must not be permanently deleted.
- Hard delete is prohibited for historical inventory data.

---

## Future Compatibility

The current design supports future expansion for:

- Barcode scanning
- QR code support
- RFID integration
- Purchase orders
- Supplier management
- Automatic replenishment
- Multi-location storage
- Batch and serial number tracking
- Expiration date tracking
- Integration with the Finance module