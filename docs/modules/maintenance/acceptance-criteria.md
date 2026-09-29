# Maintenance Acceptance Criteria — Increment 5

- Active yacht maintenance landing/list/detail/create/edit are API-backed and persisted.
- Captain can assign one or more valid CrewMembers, change status, and complete eligible work.
- Completion gets a server UTC timestamp; completed/cancelled work is never overdue.
- Planned/in-progress work is overdue exactly when due_date precedes today's UTC date.
- List sorting prioritizes unfinished work and earlier due dates deterministically.
- Loading, empty, field validation, and API error states are visible. No mock fallback is shown on API failure.
- Every endpoint requires auth, active user, tenant context and yacht context.
- Ownership is context-derived; other-yacht/tenant UUIDs do not leak existence.
- Composite database keys reject cross-tenant or cross-yacht task and assignee links.
- CrewMember assignments support multiple people and do not refer to authenticated Users.
- Completed/cancelled history is retained; no hard-delete endpoint exists.

## Deferred scope

Attachments, costs, inventory consumption, purchasing, accounting, notifications, recurring generation, preventive automation, fault intake, meters, generic Tasks, approval chains, bulk operations and dashboard metrics.

## Known risks

UTC calendar dates are used until product defines yacht-local operational dates. Maintenance type taxonomy is the existing documented set, not a configurable classification system. A cancelled task is terminal; reopening requires a future explicit product decision.
