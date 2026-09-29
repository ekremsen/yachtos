# Inventory Module

**Status:** Increment 6 operational inventory scope

## Purpose and scope

Inventory gives the Captain a reliable view of onboard stock, storage location, low-stock items, and the reasons and timing of stock changes. The first operational capability lists, creates, edits, and records stock movements for the active yacht.

`InventoryItem` is a yacht-specific item record. `StockMovement` is the immutable audit entry for each quantity change. This is not a warehouse or ERP subsystem. Categories and locations are simple item fields; this increment has no category/location administration, suppliers, purchasing, costs, transfers, or reservations.

## Stock and movement model

The item stores a denormalized `current_quantity` for efficient reads. It starts at zero; there is no unexplained initial balance. Every balance change is made by a transactional movement service which locks the item row where supported, writes the movement, and updates the balance atomically.

Movement `quantity` is a signed delta at three decimal places. `in` requires a positive delta; `out` stores a negative delta; `adjustment` accepts a non-zero signed delta. An adjustment is not an absolute count. No operation may make stock negative. Movement history also stores the resulting balance, reason, actor, and timestamp, and cannot be edited or deleted through the application.

Quantities use fixed precision decimal(12,3), never floating point. Units are `piece`, `liter`, `kilogram`, `meter`, and `pack`; no automatic conversion occurs. An item is low stock when it has a configured minimum and `current_quantity <= minimum_quantity`.

## Yacht ownership and future integrations

Items and movements belong to exactly one Tenant and Yacht, derived from server contexts. A movement can only reference an item from the same yacht and tenant. Maintenance consumption is deliberately deferred; later Maintenance integration will create normal StockMovements through the same service.

Purchasing, supplier management, accounting, invoices, barcodes, item codes, serial/batch tracking, inter-yacht transfers, warehouses, reservations, costing and automated replenishment are deferred. The Dashboard “Critical Stock” KPI remains unchanged for the dedicated dashboard increment.

See [database](database.md), [business rules](business-rules.md), [API](api.md), [UI](ui.md), and [acceptance criteria](acceptance-criteria.md).
