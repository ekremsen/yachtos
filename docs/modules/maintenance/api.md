# Maintenance API

Every endpoint uses `auth:sanctum`, `user.active`, `tenant.context`, and `yacht.context`. The backend revalidates the active yacht from `X-Yacht-Id`. Responses are no-store and use `{ "data": ... }` resources.

| Method/path | Purpose |
|---|---|
| `GET /api/maintenance` | Deterministically list current-yacht tasks with assignees and derived overdue state |
| `GET /api/maintenance/{maintenanceTask}` | Current-yacht detail; foreign and unknown UUIDs both return generic 404 |
| `POST /api/maintenance` | Create planned work; ownership derives from TenantContext/YachtContext |
| `PATCH /api/maintenance/{maintenanceTask}` | Edit allowed task fields, status and assignees |
| `POST /api/maintenance/{maintenanceTask}/complete` | Complete eligible work; server sets UTC completion timestamp |

Writable fields: title, description, type, priority, due_date, assignee_ids, and (on update) status. `tenant_id`, `yacht_id`, ownership IDs, and `completed_at` are not client controlled. Assignee IDs must identify CrewMembers in the current yacht. Completion and cancellation are terminal. Invalid field values or assignment/status rules return 422; out-of-scope records return 404.

Status values: planned, in_progress, completed, cancelled. Priority values: low, normal, high, critical. `is_overdue` is computed from status and due date against the UTC calendar date. There are no attachments, cost fields, recurrence, hard-delete, bulk, or export endpoints in this increment.
