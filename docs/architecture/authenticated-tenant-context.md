# Authenticated Tenant Context Foundation

**Status:** Implemented foundation increment

**Date:** 25 September 2026

**Authority:** [ADR-0011](../adr/ADR-0011-authenticated-tenant-foundation.md)

## Scope and ownership

- `app/Modules/Users`: UUID profile model and lifecycle fields.
- `app/Modules/Tenants`: UUID tenants, memberships and current-tenant read endpoint.
- `app/Support/Auth`: credentials, token issuance/revocation and active-user middleware.
- `app/Support/Tenancy`: scoped context and resolution middleware.
- `routes/api.php`: central routing; controllers delegate workflows.

No RBAC, public provisioning, registration, membership management, Yachts or other operational modules are implemented. The Next.js UI remains unchanged.

## Persistence and provisioning

Users have UUIDs, first/last name, lowercase trimmed unique email, optional phone/avatar, language/timezone, status and last-login time. Passwords use Laravel's configured hasher and remain hidden alongside remember tokens. Password/status are excluded from profile mass assignment; Auth owns credential operations.

Tenants and memberships use UUIDs. Foreign keys restrict deletion of referenced identities and tenants, preserving history. Memberships are not mass-assignable from HTTP input. No deletion endpoints exist.

Canonical statuses:

| Entity | Values | Database default |
|---|---|---|
| User | `active`, `inactive`, `archived` | `inactive` |
| Tenant | `active`, `suspended`, `archived` | `suspended` |
| Membership | `active`, `inactive`, `archived` | `inactive` |

Internal provisioning explicitly activates access. Test factories may create active but operationally incomplete organizations under the approved foundation exception. `active` means access availability, not completed yacht/Owner/Captain/administrator onboarding. Factories are development/test tools, not production onboarding. There is no completion flag or endpoint in this increment.

## Tenant resolution

```text
auth:sanctum → EnsureUserIsActive → ResolveTenantContext
→ CurrentTenantController → TenantResource
```

A valid membership has status `active`, `start_date <= current UTC date`, and null `end_date` or `end_date > current UTC date`. Start is inclusive; end is exclusive. These boundaries use UTC independently of display timezones.

Exactly one valid active membership must exist, and its tenant must be active. Duplicate memberships fail even when they reference the same tenant or one tenant is suspended. Historical, inactive and future memberships do not select context. Cardinality is enforced at the access boundary; there is no membership-writing endpoint yet.

Client-supplied tenant IDs in headers, query parameters or payloads are ignored, never read for lookup or authorization. No `/api/tenant/{id}` endpoint exists. Supplying an ID cannot turn missing or invalid membership into access.

`TenantContext` is a scoped container binding, not a singleton or static state. Middleware resolves it from the authenticated user and clears it in `finally`. Reading unresolved context throws rather than permitting unscoped access. Only the resolved tenant is serialized. This does not add blanket global scopes: future business modules must consume this boundary and enforce scoped queries and policies.

## Authentication

Use HTTPS in production and `Authorization: Bearer <token>`. Session cookies do not substitute for bearer tokens. No refresh flow exists.

`SANCTUM_EXPIRATION_MINUTES` defaults to `1440`, clamped to at least one minute. Each token stores `expires_at`; Sanctum also checks configured maximum age. Reducing configuration may shorten existing token validity; increasing it does not extend stored expiration. Timestamps use second precision.

Login verifies credentials, account status, membership and tenant access before issuing a token. Issuance and last-login update share a transaction. Successful login rehashes passwords when needed. Only issuance returns the plaintext token; persistence stores a hash.

Login allows five requests per normalized email/IP pair per minute and thirty per IP. Successful requests also count. Use a shared cache in production for consistent limits across workers.

Authentication logs contain event names and authenticated user/tenant identifiers where applicable, never passwords or bearer tokens.

## Endpoints

### POST /api/auth/login

```json
{"email":"captain@example.com","password":"<password>"}
```

Email is required, normalized, valid and at most 255 characters. Password is required, a string and at most 1024 characters. This endpoint creates neither identities nor memberships.

Success (`200 OK`, `Cache-Control: no-store`):

```json
{
  "data": {
    "access_token": "<issued bearer token>",
    "token_type": "Bearer",
    "expires_at": "2026-09-26T12:00:00.000000Z",
    "user": {
      "id": "<user UUID>",
      "first_name": "Cem",
      "last_name": "Arslan",
      "email": "captain@example.com"
    }
  }
}
```

Bad credentials, unknown accounts and inactive/archived accounts share a generic `401`. Valid credentials without eligible tenant access return `403`. Validation returns `422`; throttling returns `429` with retry headers.

### GET /api/tenant

Requires bearer authentication and the complete middleware chain. Success (`200 OK`, `Cache-Control: no-store`):

```json
{
  "data": {
    "id": "<tenant UUID>",
    "name": "Azure Yachting",
    "slug": "azure-yachting",
    "type": "private",
    "status": "active",
    "country": "TR",
    "timezone": "Europe/Istanbul",
    "currency": "TRY"
  }
}
```

No identities, memberships or credentials are embedded. No tenant update endpoint is implemented.

### POST /api/auth/logout

Requires a valid bearer token only; no body. Revokes only that token and returns `204 No Content`. Account deactivation, tenant suspension or membership loss do not prevent logout. Missing, expired or already-revoked tokens return `401`. Other tokens remain valid.

### Errors

```json
{"message":"Authentication failed.","code":"unauthenticated"}
```

Validation additionally includes `errors`, mapping fields to message arrays. Codes: `unauthenticated` (401), `forbidden` (403), `not_found` (404), `method_not_allowed` (405), `conflict` (409), `validation_failed` (422), `too_many_requests` (429), `server_error` (500).

API errors stay JSON without an Accept header and hide internal messages/traces even in debug mode. They use `Cache-Control: no-store` and preserve applicable retry headers.

## Migration and verification notes

Before editing the original users migration, the local SQLite database was inspected: all application tables were empty; only three scaffold migration records existed. That initial migration now defines UUID identity/profile fields and a UUID sessions foreign key. Three new migrations create tenants, memberships and Sanctum tokens.

The initial implementation left the existing SQLite file untouched. During the
subsequent pre-commit verification, the CTO explicitly authorized recreating the
empty local development schema. After verifying the effective environment was
`local`, the connection was SQLite, the exact file was
`apps/api/database/database.sqlite`, and application tables were empty, all six
migrations and the existing development seeder were run successfully. No migration
history was changed to accommodate the old local schema.

The seeder creates one development test identity with no tenant membership; it
does not provision an operational organization or grant tenant access. It is not
a production provisioning mechanism. Other databases that already applied the
old scaffold migration still need an explicitly reviewed transition. Never run
`migrate:fresh` against a persistent database without explicit authorization.

Production targets PostgreSQL; migrations use portable Laravel schema operations. Feature tests use isolated in-memory SQLite through the unchanged PHPUnit configuration. Tests must never target a database containing useful data.

From `apps/api`:

```sh
php artisan test --compact tests/Feature/Auth tests/Feature/Modules
php artisan test --compact
php artisan route:list --path=api -v
```

Coverage includes actual bearer tokens, hashing, expiry, revocation, validation, throttling, access loss, invalid/ambiguous memberships, tenant override attempts, cross-request/exception cleanup, UUID relationships, unique emails and foreign-key restrictions.

PostgreSQL runtime verification needs its PHP PDO driver and a disposable test database. Passing SQLite tests does not establish PostgreSQL verification.
