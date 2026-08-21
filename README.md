# YachtOS

YachtOS is a yacht operations platform built as a modular monolith.

## Repository Structure

```text
apps/
├── api/    Laravel backend
└── web/    Reserved for the Next.js frontend
docs/       Product, engineering, architecture, ADR, module, and roadmap documentation
```

The Laravel application currently lives in `apps/api`. The `apps/web` directory is
intentionally empty until the frontend scaffold is created in a later stage.

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

Architecture decisions and project documentation are maintained in `docs`.
Accepted ADRs must not be edited silently; changes require a superseding ADR.
