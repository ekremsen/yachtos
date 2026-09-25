# YachtOS

YachtOS is a yacht operations platform built as a modular monolith.

## Repository Structure

```text
apps/
├── api/    Laravel backend
└── web/    Next.js visual prototype
docs/       Product, engineering, architecture, ADR, module, and roadmap documentation
```

The Laravel application lives in `apps/api` and implements bearer login/logout and
current-tenant lookup. The Next.js UI in `apps/web` remains a visual prototype and
is not integrated with these endpoints.

## Backend Development

The backend requires PHP 8.2 or later, Composer, and Node.js.

```bash
cd apps/api
composer install
cp .env.example .env
php artisan key:generate
php artisan test
```

Run the development processes from `apps/api`:

```bash
composer run dev
```

## Documentation

The [authenticated tenant contract](docs/architecture/authenticated-tenant-context.md)
documents configuration, API examples, tests and migration notes. The original users
migration now uses UUIDs: databases that already applied the scaffold migration need
a reviewed transition or a separate fresh development database. The empty local
SQLite development schema was explicitly authorized for recreation during the
pre-commit verification; production databases were not modified.

Architecture decisions and project documentation are maintained in `docs`.
Accepted ADRs must not be edited silently; changes require a superseding ADR.
