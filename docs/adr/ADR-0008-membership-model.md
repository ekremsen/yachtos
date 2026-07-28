# ADR-0008 - Membership Model

**Status:** Accepted

**Date:** 02 July 2026

---

## Context

YachtOS is a multi-tenant commercial SaaS platform for yacht operations.

ADR-0007 established that a **User** is an identity and profile record only.

Business roles, operational responsibilities and organizational relationships must not be stored directly on the user record.

Users must therefore be connected to organizations and yachts through explicit, separate relationships that preserve history and support long-term product growth.

Three distinct questions arise from this requirement:

- Which organization does this user belong to?
- What operational role does this user hold on a specific yacht?
- How is this user operationally assigned to serve on a specific yacht?

Each question belongs to a different domain concern and must be answered by a different concept.

Without an explicit membership model, these relationships risk being collapsed into user records, yacht records or a single combined table. That leads to tight coupling between identity and operational context, loss of historical data, unclear module ownership and limited SaaS scalability.

This ADR defines the membership model only.

It does not define authentication, authorization, RBAC implementation, database structures or API endpoints. Those concerns are addressed in separate architectural decisions.

---

## Decision

YachtOS adopts a **membership-based relationship model**.

Users are connected to organizations and yachts through three separate concepts:

- **Tenant Membership**
- **Yacht Membership**
- **Crew Assignment**

These three concepts must remain separate in domain design and module ownership.

---

### Tenant Membership

#### Purpose

Tenant Membership represents the relationship between a user and a tenant organization.

It answers:

**"Which organization does this user belong to?"**

#### Responsibility

Tenant Membership is responsible for:

- Connecting a user to a tenant organization
- Establishing organizational access context for the user
- Preserving membership history over time

Tenant Membership is not responsible for:

- Defining what a user may do inside the organization
- Assigning operational roles on yachts
- Managing yacht-level responsibility or crew service records

#### Ownership

The **Tenants module** owns Tenant Membership.

The Users module must not own Tenant Membership. User identity is separate from organizational relationships.

#### Why It Exists

A user must belong to an organization before participating in any tenant-scoped activity on the platform.

Tenant Membership makes that organizational boundary explicit, auditable and independent of user profile data.

A user may exist before being connected to an organization. When the connection is established, it must be recorded as a Tenant Membership, not embedded in the user record.

#### Why It Is Not Yacht Membership

Tenant Membership is organizational context.

It does not describe what a user does on a yacht.

A user may hold a Tenant Membership without being assigned to any yacht. A Yacht Membership only exists when the user has a specific operational role on a specific yacht.

Tenant Membership answers "which organization" while Yacht Membership answers "what role on which yacht."

These are different questions at different scopes. Combining them would force organizational context and yacht role history into a single record, making both harder to manage independently.

---

### Yacht Membership

#### Purpose

Yacht Membership represents the operational role relationship between a user and a yacht.

It answers:

**"What operational role does this user hold on this yacht?"**

#### Responsibility

Yacht Membership is responsible for:

- Recording the operational role of a user on a specific yacht
- Defining responsibility and authority at the yacht level
- Preserving yacht role history as assignments change over time

Supported roles include:

- Owner
- Captain
- Crew
- Engineer

Yacht Membership is not responsible for:

- Organizational access context
- Crew employment or service details
- Certifications or professional qualifications
- Day-to-day crew operational records

#### Ownership

The **Yachts module** owns Yacht Membership.

Operational yacht roles belong to the yacht operational domain. Yacht Membership must not be owned by the Users module, the Tenants module or the Crew module.

Yacht roles must never be stored directly on the yacht record or the user record. They belong to Yacht Membership.

#### Why It Exists

A yacht must always know who is responsible for it.

Yacht Membership makes ownership and command structure explicit, historical and separate from user identity.

A yacht may change owners and captains over time. Each transition must be recorded in Yacht Membership history without modifying user profiles or yacht records.

At least one active Owner Yacht Membership must always exist for a yacht.

#### Why It Is Not Crew Assignment

Yacht Membership defines authority and responsibility.

Crew Assignment defines service and employment.

A Captain holds a Yacht Membership with the Captain role. That same user may also hold a Crew Assignment that records their position title, employment type, start date and certification records.

These are different answers to different questions:

- Yacht Membership: "What authority does this user have on this yacht?"
- Crew Assignment: "How and when is this user operationally serving on this yacht?"

A Crew Assignment does not carry authority. A Yacht Membership does not carry employment details.

Merging them would either add crew employment fields to an authority record or add authority fields to an employment record. Both distort the domain model and make historical records harder to reason about independently.

---

### Crew Assignment

#### Purpose

Crew Assignment represents the operational employment or service relationship of a user on a yacht.

It answers:

**"How is this user operationally assigned to serve on this yacht?"**

#### Responsibility

Crew Assignment is responsible for:

- Recording the operational position and employment context of a user on a yacht
- Preserving crew service history over time
- Supporting crew operational records such as certifications
- Tracking assignment start and end dates and employment type

Crew Assignment is not responsible for:

- Establishing organizational access context
- Defining yacht-level authority or command structure
- Storing business roles such as Owner or Captain
- Platform authentication or authorization

#### Ownership

The **Crew module** owns Crew Assignment.

Crew operational records belong to the crew operations domain. Crew Assignment must not be owned by the Users module, the Tenants module or the Yachts module.

#### Why It Exists

Yacht operations require detailed crew records beyond a role label.

A crew member may have an employment type, a start date, a position title and a set of certifications. These records serve operational and compliance purposes that are distinct from organizational access or yacht authority.

Crew Assignment makes the operational crew relationship explicit, separate and independently auditable.

#### Why It Is Not Yacht Membership

Crew Assignment is operational employment context.

Yacht Membership is authority and responsibility context.

The two concepts reference the same user-yacht relationship from different perspectives and serve different domain purposes.

A user may hold a Yacht Membership with the Engineer role and a Crew Assignment that records their full-time employment and professional certifications on the same yacht. The Yacht Membership records their authority. The Crew Assignment records how they serve.

Crew Assignment must not be used to determine yacht authority. Yacht Membership must not be used to record crew employment details.

Keeping them separate preserves the integrity of both domain concerns and allows each to evolve independently.

---

### Concepts Must Remain Separate

| Concept | Answers | Owned By | Not Responsible For |
|---------|---------|----------|---------------------|
| Tenant Membership | Which organization? | Tenants | Yacht roles, crew employment |
| Yacht Membership | What role on which yacht? | Yachts | Org context, crew service details |
| Crew Assignment | How is this user serving? | Crew | Org context, yacht authority |

A user may simultaneously hold all three relationships:

- a **Tenant Membership** in an organization,
- a **Yacht Membership** role such as Captain on a specific yacht, and
- a **Crew Assignment** with operational employment details on the same yacht.

These are complementary relationships, not alternatives to one another.

They must remain separate because they belong to different domain concerns, change for different business reasons and must be preserved independently for auditability.

### Why This Model Is Better for a Commercial SaaS

The membership-based model supports long-term SaaS growth because it:

- Preserves organizational, role and crew history independently as the platform grows.
- Avoids coupling user identity to temporary organizational or operational context.
- Allows organizational scope to grow from single-yacht operations to multi-yacht fleets without restructuring the identity model.
- Supports different operational models such as private ownership, managed operations and charter activity through flexible relationships.
- Keeps module ownership clear and maintainable as the platform adds customers, modules and complexity.
- Enables future capabilities such as ownership transfers and organizational changes without breaking existing records.

---

## Consequences

### Positive

- User identity remains stable while organizational and operational relationships change.
- Module ownership is explicit: Tenants owns org membership, Yachts owns role membership, Crew owns service assignment.
- Historical records for each relationship can be preserved and reported independently.
- The platform can scale to more organizations, yachts and operational workflows without redesigning core relationships.
- Each concept can evolve independently without breaking the others.

### Negative

- Three separate concepts must be understood rather than one combined model.
- Developers must know which concept to use for each domain question.
- Cross-module coordination is needed when creating or transitioning user relationships.
- Additional documentation and future ADRs are required to complete implementation details.

---

## Alternatives Considered

### Single Combined Membership Table

Use one relationship table to represent all three concepts together.

Rejected because:

- Three distinct domain questions would be forced into one model.
- Module ownership would become unclear.
- Organizational, authority and employment concerns would become tightly coupled.
- Independent history preservation for each relationship type would be lost.

### Roles Stored Directly on User or Yacht Records

Store Owner, Captain, Crew and Engineer as fields on the user or yacht record.

Rejected because:

- Identity and yacht records would become coupled to temporary operational state.
- Role history could not be preserved cleanly without additional structures anyway.
- The model would conflict with the separation established by ADR-0007.
- A single user may hold different roles on different yachts over time, which a fixed field cannot represent.

### Crew Assignment as the Only User-to-Yacht Relationship

Use Crew Assignment to represent both crew service details and yacht-level authority such as Owner and Captain.

Rejected because:

- Authority and employment are different domain concerns.
- Ownership and command would be modeled as employment, which is semantically incorrect.
- The Yachts module would lose clear ownership of yacht role responsibility.
- Yacht authority history and crew service history would become mixed and harder to audit separately.

### Tenant Membership Owned by the Users Module

Manage organizational membership inside the Users module instead of the Tenants module.

Rejected because:

- Organizational boundary concerns would leak into identity management.
- The Tenants module would lose ownership of its core relationship.
- Tenant isolation and organizational lifecycle management would become harder to maintain.

---

## Final Decision

YachtOS will use a membership-based relationship model with three separate concepts:

- **Tenant Membership** — user-to-organization relationship, owned by the **Tenants module**
- **Yacht Membership** — user-to-yacht operational role relationship, owned by the **Yachts module**
- **Crew Assignment** — user-to-yacht crew service relationship, owned by the **Crew module**

Tenant Membership is not Yacht Membership.

Yacht Membership is not Crew Assignment.

These concepts must remain separate in domain design, module ownership and historical records.

The Users module owns user identity only. It does not own any membership type.

This membership model is required for long-term commercial SaaS scalability in YachtOS.

Authentication, authorization and persistence details will be defined in separate architectural decisions.
