# Inventory API

Every route uses `auth:sanctum`, `user.active`, `tenant.context`, and `yacht.context`. Ownership derives from resolved server contexts. Responses use API resources and `Cache-Control: no-store`.

| Method/path | Purpose |
|---|---|
| `GET /api/inventory` | List current-yacht items with current quantity and derived low-stock state |
| `GET /api/inventory/{inventoryItem}` | Item detail with recent movement history |
| `POST /api/inventory` | Create metadata-only item with zero quantity |
| `PATCH /api/inventory/{inventoryItem}` | Update metadata; quantity and ownership are not writable |
| `POST /api/inventory/{inventoryItem}/movements` | Apply one `in`, `out`, or `adjustment` delta |

Movement body: `type`, positive `quantity` for `in`/`out` or signed non-zero `quantity` for `adjustment`, required `reason`, optional `note`. The stored delta is signed. The API atomically records the movement and resulting balance. No movement update/delete endpoint exists. Unknown and foreign-scope UUIDs both return 404; invalid metadata, precision, transitions, or insufficient stock return 422.

No category, archive, movement-report, purchasing, transfer, or Maintenance consumption endpoints are included.
