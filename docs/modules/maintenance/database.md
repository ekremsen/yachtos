# Maintenance Database Design

## `maintenance_tasks`

One row is an operational maintenance record for one yacht. It is separate from a future generic task or recurring plan.

| Column | Meaning |
|---|---|
| `id` | UUID primary key |
| `tenant_id`, `yacht_id` | Immutable tenant and yacht ownership |
| `title`, `description` | Required short title and optional work details |
| `type` | `preventive`, `corrective`, or `inspection` |
| `status` | `planned`, `in_progress`, `completed`, or `cancelled` |
| `priority` | `low`, `normal`, `high`, or `critical` |
| `due_date` | Required calendar date for planned work |
| `completed_at` | Server timestamp set only by completion action |
| timestamps | Create/update times |

## `maintenance_assignments`

Many-to-many task/CrewMember relation with its own UUID and repeated `tenant_id`, `yacht_id`. `(maintenance_task_id, tenant_id, yacht_id)` references the maintenance task's unique composite identity. `(crew_member_id, tenant_id, yacht_id)` references a CrewMember composite identity. Both use restricted update/delete. A unique `(maintenance_task_id, crew_member_id)` prevents duplicate assignments.

The CrewMember module adds the required unique `(id, tenant_id, yacht_id)` identity. Maintenance tasks similarly expose a unique `(id, tenant_id, yacht_id)` identity. Their yacht keys reference `(yachts.id, yachts.tenant_id)`. These composite foreign keys make cross-tenant and cross-yacht assignment impossible even if application checks are bypassed.

Ownership is immutable in models and server-derived on create. Tasks and assignments are not hard deleted; completed/cancelled work remains as yacht history. No Users, costs, attachments, parts, recurrence engine, or audit event table is introduced here.
