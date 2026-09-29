# Maintenance Business Rules

- A MaintenanceTask belongs to exactly one tenant and yacht. Server contexts supply ownership; clients cannot select it.
- All reads, writes, assignees, and details are scoped to the active YachtContext. Foreign-yacht IDs are indistinguishable from unknown IDs.
- A task may have multiple CrewMember assignees. Every assignee must be active and belong to the same tenant and yacht.
- CrewMember describes onboard personnel. User authenticates; YachtMembership authorizes yacht access. Neither is a maintenance assignee.
- Title, type, status, priority and due date are required. Description and assignees are optional.
- Status values: planned, in_progress, completed, cancelled. Planned may transition to in_progress, completed through the completion action, or cancelled. In-progress may complete or be cancelled. Completed/cancelled are terminal.
- Completion is an explicit action, sets `completed_at` from the server clock in UTC, and cannot be supplied or reset by client input.
- Work may be completed before its due date; scheduled dates are targets, not a restriction on recording completed work.
- Priority is low, normal, high, critical. No scoring or risk model is implied.
- `is_overdue` is derived for planned/in-progress work whose due date precedes today's UTC date. Completed and cancelled work is never overdue.
- Maintenance and assignment rows are retained; there is no hard-delete operation.
- Dates use calendar `Y-m-d`; due dates are compared in UTC. No recurrence generation or automatic rescheduling occurs.
