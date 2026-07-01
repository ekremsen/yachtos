# Database Architecture

**Version:** 1.0  
**Status:** Draft  
**Document Owner:** Engineering Team  
**Last Updated:** 29 June 2026

---

## Purpose

This document defines the database architecture of YachtOS.

The database must support long-term scalability, tenant isolation, auditability and business growth while remaining simple to maintain.

---

## Technology Choice

YachtOS will use PostgreSQL as its primary relational database.

PostgreSQL is selected because it provides:

- Excellent reliability
- Strong ACID compliance
- Advanced indexing
- JSON support
- Mature ecosystem
- Excellent performance
- Long-term scalability

---

## Design Principles

The database must be:

- Relational
- Normalized where appropriate
- Easy to understand
- Easy to migrate
- Audit-friendly
- Tenant-aware

---

## Primary Keys

Every business table must use:

- id (UUID)

UUIDs provide safer public identifiers and simplify future integrations.

---

## Foreign Keys

Every relationship must be protected using foreign key constraints.

Foreign keys must be explicitly defined.

---

## Timestamp Columns

Every business table must include:

- created_at
- updated_at

When required:

- archived_at
- deleted_at

---

## Soft Delete Strategy

Soft delete is allowed only when it has business value.

Business-critical historical records should generally be archived instead of deleted.

The use of soft delete must be documented on a module-by-module basis.

---

## Auditability

Critical business operations should be auditable.

Examples include:

- Expense approvals
- Maintenance completion
- Crew assignments
- Charter confirmations

Audit records should preserve:

- Who performed the action
- When it occurred
- What changed

---

## Tenant Isolation

Business tables should include:

- tenant_id

Every business query must be executed within the tenant context.

Cross-tenant data access is strictly prohibited.

---

## Naming Standards

### Tables

Plural snake_case

Examples:

- users
- yachts
- expenses
- maintenance_records

---

### Columns

Singular snake_case

Examples:

- tenant_id
- yacht_id
- user_id
- created_at

---

### Pivot Tables

Pivot tables should combine related table names.

Examples:

- yacht_members
- role_permissions

---

## Indexing Strategy

Indexes should be created for:

- Primary keys
- Foreign keys
- Frequently searched columns
- Frequently filtered columns

Composite indexes should be introduced only when justified by query patterns.

---

## Transactions

Database transactions must be used whenever multiple business operations must succeed or fail together.

Partial writes should never leave the system in an inconsistent state.

---

## Migration Rules

Every schema change must be performed through Laravel migrations.

Database schema changes must never be executed manually in production.

---

## Seed Strategy

The project should include seeders for:

- System roles
- Default permissions
- Default settings
- Lookup tables

Business data should never be seeded into production.

---

## Non-Goals

The database architecture will not initially include:

- Database sharding
- Multiple databases
- Event sourcing
- Polyglot persistence

These approaches may be introduced only if future business requirements justify them.

---

## Database Principle

The database is the source of truth.

Application code may change frequently.

The database structure should evolve carefully and preserve historical business data whenever possible.