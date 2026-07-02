# ADR-0007 - Identity Model

**Status:** Accepted

**Date:** 02 July 2026

---

## Context

YachtOS is a multi-tenant commercial SaaS platform for yacht operations.

Every person who accesses the platform must be represented consistently across modules such as yacht management, crew operations, maintenance, inventory and finance.

The platform must distinguish between:

- who a person is,
- how that person is verified at login,
- what that person is allowed to do,
- and what operational responsibilities that person holds on a yacht.

Without an explicit identity model, these concerns become mixed inside the Users module or stored directly on user records. That leads to tight coupling, difficult lifecycle management and poor long-term scalability.

This ADR defines the identity model only.

It does not define membership models, authorization models, authentication mechanisms or database structures. Those decisions will be recorded in future ADRs.

---

## Decision

YachtOS adopts a **profile-based identity model**.

A **User** is an identity and profile record for a person who can access YachtOS.

The Users module owns user identity and profile lifecycle only.

### What is a User?

A User represents a platform identity.

The Users module is responsible for:

- User profile information
- Personal and contact information
- User preferences
- User status and lifecycle
- Historical preservation of user identity records

A User is a person who may access YachtOS, not a description of what that person does operationally.

### What is NOT a User?

The following must not be modeled as part of the User identity:

- Authentication credentials and login sessions
- Authorization permissions
- Business roles such as Owner, Captain, Crew or Engineer
- Yacht operational responsibilities
- Tenant organizational responsibilities
- Guest records that do not require platform access

Guests, external contacts and operational assignments are separate domain concerns.

A User record answers:

**"Who is this person on the platform?"**

It does not answer:

- whether the person is authenticated,
- what the person is allowed to do,
- or which operational role the person currently holds.

### Why Business Roles Are Separated from User

Business roles describe operational responsibility.

User identity describes the person.

These are different concerns and must remain separate because:

- A person may hold different responsibilities over time.
- A person may hold different responsibilities on different yachts.
- Operational history must remain available even when responsibilities change.
- Identity must remain stable even when roles change.
- Storing business roles directly on the user record couples identity to temporary operational context.

YachtOS must never store Owner, Captain, Crew or Engineer as attributes of the User identity.

Operational responsibility belongs to separate domain relationships, not to the identity record itself.

### Why Authentication and Authorization Are Outside the Users Module

Authentication and authorization are separate architectural concerns from user identity.

- **Authentication** verifies identity.
- **Authorization** verifies permitted actions.
- **Users** stores identity and profile data.

The Users module must not own authentication or authorization because:

- Identity data changes for different reasons than access control rules.
- Authentication methods may evolve independently of user profiles.
- Authorization rules depend on tenant and operational context, not on profile fields.
- Mixing these concerns inside the Users module would violate module boundaries and increase maintenance cost.

Authentication and authorization belong to dedicated platform concerns outside the Users module.

The Users module provides the identity record that other parts of the system reference.

It does not verify login credentials or enforce permissions.

### Why This Model Is Better for a Commercial SaaS

This identity model supports long-term commercial SaaS requirements because it:

- Keeps identity stable while operational relationships change.
- Allows the product to grow from single-yacht operations to multi-yacht and multi-organization usage without redesigning the user record.
- Makes module ownership clear for engineering, documentation and onboarding.
- Allows authentication capabilities such as MFA, SSO and external identity providers to be added without changing the core identity model.
- Allows authorization rules to evolve without rewriting user profile logic.
- Preserves auditability by separating identity history from changing operational assignments.
- Reduces coupling between business modules and platform access concerns.
- Improves maintainability as the number of modules, customers and developers increases.

The identity model is intentionally narrow.

YachtOS gains flexibility by keeping identity simple and moving operational and access concerns to their own domains.

---

## Consequences

### Positive

- Clear separation between identity, authentication and authorization.
- User records remain stable as operational responsibilities change.
- The Users module has a focused and maintainable responsibility.
- Future authentication and authorization changes do not require redesign of the identity model.
- Better long-term scalability for a commercial SaaS product.
- Easier documentation, testing and module ownership.
- Reduced risk of role and permission logic leaking into profile management.

### Negative

- Identity alone is not sufficient to determine access or operational responsibility.
- Other domain models must explicitly connect users to organizations and operational context.
- Developers must understand multiple related concepts instead of a single combined user object.
- Additional documentation and ADRs are required to complete the full access model.

---

## Alternatives Considered

### Combined User and Role Model

Store business roles such as Owner, Captain, Crew and Engineer directly on the user record.

Rejected because:

- Identity becomes coupled to temporary operational context.
- Role history is difficult to preserve cleanly.
- The model does not scale well as users change yachts or responsibilities over time.
- SaaS growth requires flexible relationships rather than fixed role columns.

### Users Module Owns Authentication

Implement login, credentials and sessions inside the Users module.

Rejected because:

- Authentication evolves independently from profile management.
- Security concerns should not be mixed with business profile logic.
- Module boundaries become unclear.
- Future authentication methods would force repeated changes to the Users module.

### Identity Embedded in Operational Modules

Define users separately inside yacht, crew or tenant modules instead of as a shared platform identity.

Rejected because:

- The same person would be duplicated across modules.
- Profile management would become inconsistent.
- Cross-module references would become harder to maintain.
- A shared SaaS platform requires one canonical identity concept.

---

## Final Decision

YachtOS will use a profile-based identity model.

A **User** is a platform identity and profile record.

The Users module owns identity and profile lifecycle only.

Business roles, authentication and authorization are intentionally separated from the User identity.

This separation is the foundation of the YachtOS identity architecture and is required for long-term commercial SaaS scalability.

Membership models, authorization models and authentication mechanisms will be defined in future ADRs.
