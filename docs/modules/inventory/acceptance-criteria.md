# Inventory Acceptance Criteria — Increment 6

- Current active-yacht items can be listed, opened, created at zero, and edited without changing ownership or stock.
- Captain can record stock in, out, and signed adjustment; quantity and immutable movement are committed atomically.
- Negative stock is rejected without changing balance or history. Decimal quantities up to three fractional digits work.
- Item detail shows recent movement type, delta, resulting balance, reason/note, actor, and timestamp.
- Low stock is derived when a configured minimum is greater than or equal to current stock.
- Foreign-yacht and foreign-tenant items remain hidden and return generic not-found detail behavior; database boundaries reject cross-scope movement links.
- Loading, empty, validation, and API error states are visible; API failure does not show mock stock.
- Items start at zero; every non-zero seeded balance has deterministic StockMovement history.
- All routes are protected and ownership is server-derived.

## Deferred scope

Purchasing, suppliers, accounting/costing, invoices, barcodes, transfers, warehouses, reservations, automated replenishment, serial/batch tracking, Maintenance consumption, category/location administration, and Dashboard aggregation.

## Known risks

SQLite does not exercise real row-level locking. Production concurrent stock mutations should use a database such as PostgreSQL. Quantities are capped at decimal(12,3); yacht-local display conventions and unit conversion remain future product decisions.
