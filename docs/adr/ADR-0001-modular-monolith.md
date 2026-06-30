# ADR-0001 - Modular Monolith Architecture

**Status:** Accepted

**Date:** 29 June 2026

---

## Context

YachtOS is expected to grow into a commercial SaaS platform with multiple business domains such as yacht management, crew management, maintenance, inventory and finance.

The architecture must support long-term growth without introducing unnecessary complexity during the early stages of development.

---

## Decision

YachtOS will use a **Modular Monolith** architecture.

The application will be developed as a single deployable application while maintaining clear boundaries between business modules.

---

## Consequences

### Positive

- Faster development.
- Easier debugging.
- Simpler deployments.
- Lower infrastructure cost.
- Easier refactoring.
- Better developer experience.

### Negative

- Requires discipline to keep module boundaries clean.
- Future extraction into microservices may require planning.

---

## Alternatives Considered

### Microservices

Rejected because they introduce operational complexity that is not justified for the MVP.

### Layered Monolith

Rejected because business modules become tightly coupled over time.

---

## Final Decision

A Modular Monolith provides the best balance between simplicity, scalability and maintainability for YachtOS.