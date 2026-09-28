# Crew Module

**Status:** Increment 4 operational roster scope

## Business purpose

Crew answers the yacht operations question: **who is serving aboard this yacht?** A CrewMember is an operational person with a yacht assignment. It does not require YachtOS credentials.

`User` is an authenticated platform identity. `YachtMembership` authorizes a User to operate a yacht and carries the minimal authorization role. `CrewMember` records an onboard person, their contact details, position, employment status and service dates. These relationships are not interchangeable; crew creation does not create Users or YachtMemberships.

## Increment 4 scope

The active yacht's roster can be viewed, created and edited. Crew records belong to exactly one tenant and yacht; ownership is derived from the server's `TenantContext` and `YachtContext`. Inaccessible records are returned as not found. Records are never hard deleted; mark a member inactive and set an end date to preserve service history.

Profiles are intentionally minimal: names are required; position, contact details, nationality, status and service dates are optional where appropriate. Salary, payroll, recruitment, medical data, passport documents, certifications, scheduling, leave and advanced HR workflows are deferred.

## Rules and acceptance

- A CrewMember has one immutable tenant and yacht.
- User supplied tenant/yacht identifiers never select ownership.
- End date must be later than start date when both are set.
- Only `active` and `inactive` status are supported in this first operational scope.
- Current-yacht access is enforced in every query and database composite foreign key.
- List, detail, create and edit work in the existing Crew UI; loading, empty, validation and API failure states are visible.

See [database](database.md), [business rules](business-rules.md), [API](api.md), [UI](ui.md), and [acceptance criteria](acceptance-criteria.md).
