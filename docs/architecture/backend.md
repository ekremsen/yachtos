# Backend Architecture

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the backend architecture direction for YachtOS.

The backend must provide a secure, maintainable and scalable API foundation for the web application, future mobile applications and possible external integrations.

---

## Technology Choice

YachtOS backend will be built with Laravel.

Laravel is selected because it provides:

- Strong ecosystem
- Fast development speed
- Mature authentication options
- Built-in validation
- Queue support
- Testing support
- Good developer experience
- Strong suitability for SaaS applications

---

## Architecture Style

The backend will follow a Modular Monolith architecture.

Business domains will be organized as internal modules while the application remains a single deployable system.

---

## Module Boundaries

Each business module should own its own:

- Business rules
- Application services
- Models
- API controllers
- Validation requests
- Policies
- Tests

Modules must not directly depend on internal implementation details of other modules.

---

## Controller Responsibilities

Controllers may only:

- Receive requests
- Validate input
- Call application services
- Return responses

Controllers must never contain business logic.

---

## Service Layer

Business workflows must live inside application services.

Services are responsible for coordinating business logic, enforcing rules and calling persistence layers when needed.

---

## Validation

Request validation must be explicit.

Validation should be handled before business logic is executed.

---

## Authorization

Authorization must be enforced through policies, permissions or dedicated authorization services.

Authorization must always consider:

- Authenticated user
- Tenant context
- Yacht context when applicable
- User permissions
- Resource ownership

---

## Error Handling

The backend must return consistent API error responses.

Errors must not expose internal implementation details.

---

## Testing

Backend modules must include:

- Unit tests for business rules
- Feature tests for API endpoints
- Authorization tests
- Tenant isolation tests

---

## Non-Goals

The backend will not start with:

- Microservices
- Event sourcing
- CQRS
- Complex domain event infrastructure
- Distributed transactions

These patterns may be considered only when business needs justify them.

---

## Backend Principle

Keep the backend explicit, modular and boring.

Boring architecture is easier to maintain, easier to debug and easier to scale when the product grows.