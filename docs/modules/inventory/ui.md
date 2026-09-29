# Inventory UI

The existing YachtOS layout and visual language remain unchanged. Live screens:

- `/inventory`: API-backed summary of item count, low stock, zero-stock items, and priority items.
- `/inventory/items`: searchable real item list with category, balance/unit, minimum, location, and low-stock badge.
- `/inventory/items/{uuid}`: item details and recent movements with signed change, resulting balance, reason/note, actor, and time.
- `/inventory/items/new` and `/inventory/items/{uuid}/edit`: metadata form. New items always begin at zero.
- Item detail includes stock-in, stock-out, and signed adjustment controls; the reason is required.

All requests use the centralized client and active `X-Yacht-Id`. Loading, empty, validation, and API failure states are visible, with no fake data fallback. Movement results reload from the server. Legacy movement, locations, counts, alerts, categories, and report prototypes are not operational screens in this increment; purchasing and dashboard data are deferred.
