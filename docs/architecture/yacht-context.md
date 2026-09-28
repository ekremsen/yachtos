# Yacht Context — Increment 3

## Boundaries and membership

A **Tenant** is the organization boundary. A **Yacht** is an operational workspace owned by exactly one tenant. Tenant access alone does not grant access to a yacht. A user must have an active `YachtMembership` tied to an active `TenantMembership`, and both memberships must be valid for the current UTC date. Yacht membership roles remain the minimal temporary values in the schema; crew positions and expanded RBAC are outside this increment.

The yacht belongs to the Yachts module. Yacht memberships record the tenant, yacht, user and parent tenant membership. The database adds composite unique identities on `(yachts.id, yachts.tenant_id)` and `(tenant_memberships.id, tenant_memberships.tenant_id, tenant_memberships.user_id)`. Composite foreign keys require both yacht and parent membership to share the declared tenant and user boundary. Runtime model validation gives an early field error; database constraints remain the final integrity guarantee.

## Request lifecycle

The protected yacht route runs middleware in this order:

```text
auth:sanctum → user.active → tenant.context → yacht.context → controller
```

`TenantContext` is resolved first. `YachtContext` is a separate Laravel scoped binding and is populated only by `ResolveYachtContext`. Both contexts are cleared in `finally`, including when downstream code fails. Unresolved access throws. Yacht lookup is scoped to the resolved tenant and authenticated user on every request; a supplied UUID is never evidence of authorization.

Membership start dates are inclusive (`start_date <= UTC today`); end dates are exclusive (`end_date > UTC today`). Inactive yacht memberships, inactive yachts, and inactive/expired tenant memberships confer no yacht access.

## API

All responses use `Cache-Control: no-store`.

### `GET /api/yachts`

Requires authentication, an active user and exactly one valid active tenant context. Returns only accessible active yachts, ordered by name and UUID:

```json
{"data":[{"id":"<uuid>","name":"M/Y Azure","status":"active","home_port":"Göcek Marina"}]}
```

### `GET /api/yacht`

Uses the same authentication and tenant middleware, followed by yacht resolution.

- Zero accessible yachts: `403`, generic `Access is unavailable.`
- Exactly one accessible yacht: resolves automatically.
- Multiple yachts without a selection: `409`, generic conflict response.
- Explicit selection: provide `X-Yacht-Id: <uuid>`; the server rechecks membership, yacht status and tenant boundaries before resolving it.
- Malformed, nonexistent or inaccessible selection: generic `403`, without confirming yacht existence.

`GET /api/tenant` and `POST /api/auth/logout` do not require yacht membership. Tenant identity remains separately resolved and cannot be selected by client headers.

## Frontend selection

After login or session restore, the browser resolves `/api/tenant` and fetches `/api/yachts`. It stores only the selected yacht UUID in `localStorage`; no yacht name, membership, role or authority is cached. Before using a saved ID after reload, it sends `X-Yacht-Id` to `/api/yacht`, where it is reauthorized. If that selection is inaccessible, it is discarded. One remaining accessible yacht auto-resolves; multiple yachts require selection. Selection errors do not grant access. The shell displays the resolved yacht from API state.

## Verification limits

SQLite feature tests exercise migrations, model validation and composite foreign-key behavior. PostgreSQL is the production target; local runtime verification is reported separately because it requires the PHP PostgreSQL PDO driver and a disposable PostgreSQL database. SQLite success does not prove PostgreSQL runtime behavior.
