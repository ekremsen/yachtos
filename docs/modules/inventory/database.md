# Inventory Database Design

## `inventory_items`

| Column | Design |
|---|---|
| `id` | UUID primary key |
| `tenant_id`, `yacht_id` | Required ownership, composite yacht boundary |
| `name` | Required string, max 180 |
| `description` | Optional text |
| `category` | Required simple string, max 80; no category table in this increment |
| `unit` | Validated enum: piece, liter, kilogram, meter, pack |
| `current_quantity` | Decimal(12,3), required, starts at 0 |
| `minimum_quantity` | Nullable Decimal(12,3), non-negative |
| `storage_location` | Optional string, max 120 |
| timestamps | Created/updated |

`current_quantity` is a denormalized read balance; successful stock operations update it only together with a StockMovement in the same transaction. Do not use floats for persisted quantities or balance arithmetic. Decimal quantities are represented as integer thousandths inside the application service to avoid binary floating-point drift.

## `stock_movements`

Each immutable row has UUID, `tenant_id`, `yacht_id`, `inventory_item_id`, `performed_by_user_id`, `type` (`in`, `out`, `adjustment`), signed `quantity` delta Decimal(12,3), `balance_after` Decimal(12,3), required `reason`, optional `note`, and creation timestamp. There is no normal movement update/delete operation. The actor references the authenticated User for audit attribution.

Composite foreign keys bind `(yacht_id, tenant_id)` to Yacht and `(inventory_item_id, tenant_id, yacht_id)` to the matching InventoryItem identity. Restricted deletes/updates preserve history. Item IDs are also unique with tenant and yacht for this boundary.

## Decisions and risks

No category master table, archive status, item code/SKU, initial quantity input, separate locations table, or MaintenanceTask foreign key is added. Items start with zero stock and must receive an explicit `in` movement to establish stock. Stock history prevents hard deletion. Row-level `lockForUpdate` is used within a database transaction; SQLite ignores row-level locks, so production deployments requiring concurrent stock writes must use a database with real row locks (for example PostgreSQL). Composite database keys enforce yacht/tenant ownership. Application validation and the locked mutation service enforce nonnegative balances and movement semantics.
