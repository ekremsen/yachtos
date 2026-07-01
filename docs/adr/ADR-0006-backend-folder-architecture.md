# ADR-0006: Backend Folder Architecture

**Status:** Accepted

**Date:** 01 July 2026

**Decision Makers:**
- Product Team
- Engineering Team

---

# Context

YachtOS will be developed as a Laravel-based Modular Monolith.

The project contains multiple business domains such as Users, Tenants, Yachts, Crew, Maintenance, Inventory, Finance and Charter.

A consistent backend folder architecture is required to keep the codebase maintainable as the product grows.

---

# Decision

YachtOS will use a custom Modular Monolith folder structure inside Laravel.

Business modules will live under:

app/Modules

Shared cross-module code will live under:

app/Shared

Framework-adjacent support code will live under:

app/Support

---

# Application Structure

The backend application should follow this structure:

app/
├── Modules/
│   ├── Users/
│   ├── Tenants/
│   ├── Yachts/
│   ├── Crew/
│   ├── Maintenance/
│   ├── Inventory/
│   ├── Finance/
│   └── Charter/
├── Shared/
└── Support/

---

# Module Structure

Each module should follow a consistent internal structure.

Example:

app/Modules/Yachts/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
├── Services/
├── Policies/
├── Data/
├── Actions/
├── Enums/
└── Tests/

---

# Module Ownership

Each module owns its own:

- Controllers
- Requests
- Models
- Services
- Policies
- Data objects
- Actions
- Enums
- Tests

Modules should avoid depending on internal implementation details of other modules.

---

# Shared Directory

The Shared directory contains reusable code that is not owned by a single business module.

Examples:

app/Shared/
├── Http/
├── Exceptions/
├── Responses/
├── ValueObjects/
├── Data/
└── Support/

Shared code must remain generic and business-neutral.

---

# Support Directory

The Support directory contains framework-adjacent infrastructure and technical helpers.

Examples:

app/Support/
├── Tenancy/
├── Auth/
├── Files/
├── Notifications/
└── Pagination/

Support code may be technical, but it should not contain business rules.

---

# Documentation Alignment

Documentation, code and tests should follow the same domain names.

Example:

docs/modules/yachts

maps to:

app/Modules/Yachts

and:

tests/Feature/Modules/Yachts

This ensures that every business domain is easy to locate across the repository.

---

# Alternatives Considered

## Standard Laravel Structure

Rejected because domain code would be scattered across controllers, models, policies and services.

This becomes harder to maintain as the application grows.

---

## nWidart Laravel Modules

Rejected for the MVP because it introduces package-level conventions and dependency.

YachtOS needs modular discipline, not package dependency.

---

## Microservices

Rejected because they introduce operational complexity that is not justified at the current stage.

---

# Consequences

## Positive

- Clear module ownership
- Easier navigation
- Better long-term maintainability
- Strong alignment between documentation and code
- Easier onboarding for future developers
- Reduced risk of domain logic becoming scattered

## Negative

- Requires discipline to maintain module boundaries
- Slightly less conventional than default Laravel structure
- Some Laravel auto-discovery conventions may require explicit configuration

---

# Final Decision

YachtOS will use a custom Laravel Modular Monolith structure.

Business domains will be organized under app/Modules.

Shared reusable code will live under app/Shared.

Technical infrastructure helpers will live under app/Support.