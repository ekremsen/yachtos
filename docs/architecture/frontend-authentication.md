# Frontend authentication integration — Increment 2

Implemented against the existing [bearer API contract](authenticated-tenant-context.md)
and [ADR-0011](../adr/ADR-0011-authenticated-tenant-foundation.md). No domain,
tenant-selection, RBAC, cookie-authentication or onboarding changes are introduced.

## Ownership and flow

- `apps/web/src/lib/api/client.ts`: one configured API base URL, platform `fetch`,
  JSON and bearer headers, no cookies, no caching, a ten-second request timeout,
  normalized HTTP/network errors and structured validation errors.
- `src/lib/auth/session.ts`: minimal identity, token and expiry persistence under
  `yachtos.auth.v1`; corrupt/expired records are discarded. Passwords and tenant
  identifiers are never persisted or sent as tenant selectors.
- `src/lib/auth/auth-provider.tsx`: React context for identity, tenant and session
  status. Stale asynchronous responses cannot restore a cleared/replaced session.
- `src/components/login-screen.tsx`: the existing visual login design with real
  submission, duplicate prevention, field errors and safe credential errors.
- `AppShell` gates application content using `RequireAuth`. Public login,
  password-reset and invitation placeholder screens expose no protected content;
  the unimplemented forms cannot submit or simulate successful authentication.

Login posts credentials to `/api/auth/login`, then immediately resolves
`GET /api/tenant` with the issued token. Only successful tenant resolution permits
persisting the session and navigating to `/dashboard`. If that step or browser
storage fails, access stays blocked and revocation of the new token is attempted.

Initialization restores the stored token and revalidates `/api/tenant` before
rendering the shell. Protected-route changes, browser focus/visibility return and
storage changes revalidate it. `401` and `403` clear local state and protected
routes return to login. A local expiry check also ends sessions at `expires_at`.
Network/server failures hide protected content and offer retry/logout without
silently destroying a potentially valid saved token.

Logout clears both storage locations and in-memory state, redirects to login, and
calls `/api/auth/logout` with the captured token. The local logout completes even
when the API fails. Successful revocation invalidates that token only. If the API
is unreachable, server-side revocation cannot be guaranteed; the token remains
subject to its configured expiry. No refresh tokens are introduced.

The authenticated identity and tenant name appear in the existing desktop header.
Organization settings display the tenant name read-only. `M/Y Azure`, operational
screens and statistics remain prototypes. The existing role selector is disabled
and labelled as a prototype; it grants no permissions.

## Storage and security

“Beni hatırla” checked uses `localStorage`; unchecked uses tab `sessionStorage`.
Both survive reloads, but the latter normally ends with the tab. This choice does
not change the backend's default 24-hour lifetime. Cached identity is display data,
not authority, and is refreshed only by a subsequent login (there is no `/me`).
Tenant information is reloaded from Laravel, never selected by the client.

Both storage mechanisms are readable by JavaScript and vulnerable to token theft
through XSS. They are **not equivalent to HttpOnly cookies**. Use HTTPS and trusted
scripts in deployment; never log bearer tokens or render untrusted HTML. The
client route gate is a UI control, not a security boundary: Laravel continues to
enforce authentication, active membership and tenant isolation on every protected
API request. Next.js does not receive the token for server-side rendering.

## Local setup

From `apps/api`, with an existing local `.env` and application key:

```sh
php artisan migrate
php artisan db:seed
php artisan serve --host=127.0.0.1 --port=8000
```

No schema changes or resets are required for Increment 2. `DatabaseSeeder` runs
only in `local` or `testing`; it does nothing in production/staging, even if invoked
with `--force`. Re-running it restores its known fixture password/status and reuses
the same user, tenant and membership. It does not delete unrelated records.

| Development-only fixture | Value |
|---|---|
| Email | `captain@azureyachting.com` |
| Password | `YachtOS-Dev-2026!` |
| Tenant | `Azure Development` (`azure-development`) |
| Membership | Active, no end date |

This is an operationally incomplete foundation organization; the displayed name
“Captain” is fixture text, not an assigned role. No Yacht records are created.
Never provision these known credentials in a real deployment.

From `apps/web`:

```sh
npm ci
cp .env.example .env.local
npm run dev
```

Set `NEXT_PUBLIC_API_URL=http://localhost:8000` (origin only; the client adds
`/api`). This public value is embedded at build time: rebuild when it changes.
Local environment files are ignored. `npm run build` followed by `npm start` runs
the production frontend locally. Node 22.18+ or Node 24 is recommended for the
built-in TypeScript-stripping test runner; no testing dependencies were added.

Laravel permits exactly `http://localhost:3000` and `http://127.0.0.1:3000` by
default in `local`. Configure comma-separated `CORS_ALLOWED_ORIGINS` for other
origins. Outside local, an unset list defaults to empty. Production must explicitly
set its HTTPS frontend origins, API URL, `APP_ENV=production` and `APP_DEBUG=false`.
CORS permits GET/POST/OPTIONS and Accept/Content-Type/Authorization; cookies are
disabled. CORS is a browser policy, not a replacement for authentication.

## Verification — 25 September 2026

- Full backend suite: **55 tests, 264 assertions passed**, including all prior
  authentication/tenant-isolation tests plus CORS and idempotent/production-safe
  fixture tests. PHPUnit configuration and migrations are unchanged.
- Frontend: **8 built-in Node tests passed** (storage selection, corrupt/expired
  sessions, unavailable storage, independent storage fallback, request headers,
  empty logout, normalized errors).
- TypeScript and Next.js production build passed. No lint script is configured.
- Direct HTTP checks against the local Laravel API passed for anonymous access,
  invalid credentials, valid login, allowed-origin CORS, tenant resolution, token
  reuse, logout and rejection of the revoked token. The verification token was
  revoked during cleanup. The local server needed to run outside the filesystem
  sandbox to write rate-limit/token records.
- Browser verification could not be repeated in this review: the browser tool
  reported no available browser surfaces and rejected opening the in-app browser.
  Earlier uncommitted notes reported successful browser checks, but those results
  are not independently verified here. No browser dependency was added.

| Manual flow | Observed result |
|---|---|
| Anonymous `/dashboard` | Browser check pending; anonymous API access returns `401` |
| Invalid credentials | HTTP `401` verified; browser error display pending |
| Valid credentials | HTTP login and tenant resolution verified; dashboard navigation pending |
| Valid reload | Storage unit tests pass; browser reload with both choices pending |
| Tenant display | API returns `Azure Development`; visual display pending |
| Expired token | Backend tests pass; browser redirect pending |
| Logout | HTTP `204` verified; browser clearing and redirect pending |
| Revoked/invalid token | HTTP revoked-token `401` and backend tests pass; browser redirect pending |

Commit readiness: automated checks pass, but final Increment 2 sign-off remains
pending the browser checks above. No claim of complete manual verification is made.

PostgreSQL runtime verification remains outstanding because the local PHP runtime
has no PostgreSQL PDO driver. The existing Composer transitive advisories and
vendor duplicate-class warnings from Increment 1 were not remediated in this
authentication integration; no dependencies were upgraded.

## Increment file inventory

Created:

- `apps/api/config/cors.php`
- `apps/api/tests/Feature/CorsTest.php`
- `apps/api/tests/Feature/DevelopmentSeederTest.php`
- `apps/web/.env.example`
- `apps/web/src/components/login-screen.tsx`
- `apps/web/src/lib/api/client.ts`
- `apps/web/src/lib/auth/auth-provider.tsx`
- `apps/web/src/lib/auth/session.ts`
- `apps/web/tests/auth.test.mjs`
- `docs/architecture/frontend-authentication.md`

Modified:

- `.gitignore`
- `README.md`
- `apps/api/.env.example`
- `apps/api/database/seeders/DatabaseSeeder.php`
- `apps/web/package.json`
- `apps/web/src/app/layout.tsx`
- `apps/web/src/components/app-shell.tsx`
- `apps/web/src/components/screen-renderer.tsx`
- `docs/architecture/authenticated-tenant-context.md`

Git classification: **19 expected files** — **5 backend** source/configuration/test
files, **10 frontend** source/configuration/test files, **3 documentation** files,
and the shared **`.gitignore`**.
No generated files or secrets are staged. Local `.env.local`, SQLite, build
outputs, dependencies and caches are ignored. No commit or push was performed.
