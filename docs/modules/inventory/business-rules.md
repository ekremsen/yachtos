# Inventory Business Rules

- An InventoryItem belongs to one Tenant and one Yacht. Ownership comes from TenantContext and YachtContext and never from request payloads.
- Every StockMovement belongs to an InventoryItem in the same tenant and yacht. Foreign-yacht/tenant item IDs return a generic not-found response.
- Item metadata includes name, optional description, simple category and location strings, unit, and optional minimum quantity. Categories are not managed as a separate subsystem.
- `current_quantity` is a denormalized balance. New items start at zero; establishing a balance requires an explicit stock-in operation.
- All quantities use decimal(12,3) with maximum three fractional digits. Units are piece, liter, kilogram, meter, and pack. Units are not converted.
- `in` and `out` request positive quantities; the stored movement delta is respectively positive and negative. `adjustment` quantity is a signed delta. It must be non-zero; a negative adjustment cannot take the balance below zero.
- A stock movement and balance update succeed or fail together in one database transaction. The item row is locked for mutation where the database supports row-level locking.
- Stock cannot become negative. A rejected operation creates no movement and changes no balance.
- Movement reason is required. The immutable history records type, signed delta, resulting balance, reason, optional note, authenticated actor, and timestamp. Corrections require another adjustment movement.
- An item is low stock when minimum quantity is configured and current quantity is less than or equal to the minimum.
- StockMovements and items with movement history cannot be hard deleted through the application.
- Maintenance consumption, purchasing, supplier relationships, cost, and replenishment are out of scope.
