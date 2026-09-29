# Maintenance Module

**Status:** Increment 5 operational maintenance scope

## Purpose and scope

Maintenance helps a Captain answer what work a yacht needs, what is overdue or due soon, who is responsible, and what has been completed. The first operational capability records, assigns, edits, and completes maintenance work for the active yacht.

The domain record is `MaintenanceTask`. It is a maintenance-specific operational record, not the future generic Tasks module and not an approval-heavy WorkOrder workflow. The existing Work Orders navigation is retained as the maintenance work list. Maintenance status is separate from any future recurring plan or schedule.

Maintenance belongs to exactly one Tenant and one Yacht. Its ownership is derived from the authenticated server contexts. Assignments refer to one or more `CrewMember` records, never authenticated Users or YachtMemberships. A CrewMember is onboard personnel; YachtMembership is yacht access for an authenticated User.

## Lifecycle

The statuses reflect the existing documented lifecycle: `planned`, `in_progress`, `completed`, and `cancelled`. Planned work may start, complete, or be cancelled; in-progress work may complete or be cancelled. Completed and cancelled records are terminal and retained. Completion sets a server-generated `completed_at`; it is never client supplied.

Priorities are `low`, `normal` (the former documented Medium), `high`, and `critical`. Overdue is derived when a task is unfinished (`planned` or `in_progress`) and `due_date` is earlier than the current UTC date. No overdue flag is stored.

## In scope

- Maintenance landing summary and operational list
- Detail, create, edit, assignee selection, status changes and completion
- Due date, priority, type, description and multiple CrewMember assignments
- History retention; no hard-delete endpoint

Attachments, costs, inventory use, purchasing, accounting, automatic recurrence, notification delivery, preventive-plan generation, fault intake, meters, generic tasks, and approval workflows remain deferred. Other prototype Maintenance routes are not represented as live records. The Dashboard metric remains mock data for the dedicated dashboard increment.

See [database](database.md), [business rules](business-rules.md), [API](api.md), [UI](ui.md), and [acceptance criteria](acceptance-criteria.md).
