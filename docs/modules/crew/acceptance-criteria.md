# Crew Acceptance Criteria — Increment 4

- Authenticated Captain can open the active yacht's real roster.
- Detail, create, and edit use persisted API records.
- Loading, empty, validation, and recoverable API failure states are clear.
- A saved change remains after reload.
- Every endpoint requires the complete auth, user, tenant, and yacht middleware chain.
- Queries are restricted to YachtContext; foreign yacht IDs do not disclose existence.
- Create derives ownership from contexts; update cannot move a member.
- Composite database key blocks a cross-tenant yacht reference.
- Crew records do not require or create authentication Users.
- No hard deletion or speculative HR/payroll/sensitive data is introduced.

## Deferred

Certifications, documents, scheduling, leave, payroll, bulk operations, pagination, advanced search/filtering, full assignment history, and HR workflows remain outside the increment.

## Risks and limits

Email uniqueness and duplicate-person resolution are not defined in this increment. Assignment overlap and rehire history need a future product decision before implementing multiple service periods for one person.
