# Architecture Decision Records

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 01 July 2026

---

## Purpose

This directory contains Architecture Decision Records for YachtOS.

ADRs document important technical and architectural decisions made during the development of the project.

---

## ADR Rules

- Every important architectural decision must be documented.
- ADRs must explain the context, decision and consequences.
- ADRs must be written in English.
- ADRs must be immutable after acceptance unless superseded by a new ADR.
- New decisions should create new ADR files instead of silently editing old decisions.

---

## Naming Convention

ADR files must follow this format:

ADR-0001-example-decision.md

Examples:

- ADR-0001-modular-monolith.md
- ADR-0002-use-rest-api-for-mvp.md
- ADR-0003-rest-endpoint-conventions.md
- ADR-0004-frontend-stack.md
- ADR-0005-design-system.md
- ADR-0006-backend-folder-architecture.md
- ADR-0007-identity-model.md
- ADR-0008-membership-model.md
- ADR-0009-authorization-model.md
- ADR-0010-tenant-context-strategy.md
- ADR-0011-authenticated-tenant-foundation.md

---

## Current ADRs

| ADR | Title | Status |
|-----|-------|--------|
| ADR-0001 | Modular Monolith Architecture | Accepted |
| ADR-0002 | Use REST API for MVP | Accepted |
| ADR-0003 | REST Endpoint Conventions | Accepted |
| ADR-0004 | Frontend Technology Stack | Accepted |
| ADR-0005 | Design System Strategy | Accepted |
| ADR-0006 | Backend Folder Architecture | Accepted |
| ADR-0007 | Identity Model | Accepted |
| ADR-0008 | Membership Model | Accepted |
| ADR-0009 | Authorization Model | Accepted |
| ADR-0010 | Tenant Context Strategy | Accepted |
| ADR-0011 | Authenticated Tenant Context Foundation | Accepted |

---

## ADR Principle

Architecture decisions must be explicit, traceable and easy to understand.

A future developer should be able to understand not only what decision was made, but why it was made.
