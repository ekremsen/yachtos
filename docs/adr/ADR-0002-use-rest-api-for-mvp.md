# ADR-0002 - Use REST API for MVP

**Status:** Accepted

**Date:** 29 June 2026

---

## Context

YachtOS will start as a SaaS platform focused on operational workflows for luxury yacht captains.

The MVP requires predictable, secure and easy-to-document APIs for web and future mobile clients.

---

## Decision

YachtOS will use REST API for the MVP.

GraphQL will not be used in the initial version.

---

## Reasons

- REST is simpler to implement.
- REST works naturally with Laravel.
- REST is easier to document and test.
- REST is easier to secure with standard middleware.
- REST is sufficient for the MVP workflows.
- REST is easier for future external integrations.

---

## Alternatives Considered

### GraphQL

Rejected for the MVP because it introduces additional complexity in schema management, authorization, caching and query performance.

---

## Consequences

### Positive

- Faster MVP delivery.
- Lower architectural complexity.
- Easier onboarding for future developers.
- Clearer API documentation.

### Negative

- Some client-specific data fetching may require multiple requests.
- GraphQL may still be reconsidered in the future if product needs justify it.

---

## Final Decision

YachtOS will use REST API for the MVP and reconsider GraphQL only if there is a validated business or technical need.