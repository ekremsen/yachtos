# Crew API

All endpoints require `auth:sanctum`, `user.active`, `tenant.context`, and `yacht.context`. The active yacht is auto-resolved or selected with `X-Yacht-Id`. The header is authorization input that the backend rechecks; body ownership fields are ignored/rejected and never trusted.

| Method and path | Behavior |
|---|---|
| `GET /api/crew` | List current yacht members, ordered by last name, first name |
| `GET /api/crew/{crewMember}` | Return one current-yacht member; other yacht and unknown IDs both return `404` |
| `POST /api/crew` | Create from validated profile fields; tenant and yacht come from contexts |
| `PATCH /api/crew/{crewMember}` | Update editable profile, status and dates; ownership cannot change |

The JSON envelope is `{ "data": ... }`. Resources expose `id`, names, position, contact details, nationality, status and service dates, not tenant/yacht ownership. Responses are not cached. Validation errors are `422`; inaccessible records are `404`; authentication/context errors follow the existing API contracts.

## Create/update input

Allowed fields: `first_name`, `last_name`, nullable `position`, `email`, `phone`, `nationality`, `status`, nullable `start_date`, nullable `end_date`. Names are required on create and optional on patch; status is `active` or `inactive`. Email is validated if present; nationality uses an ISO-style two-letter code. End date must be after start date when both values are supplied, including against the persisted counterpart during partial updates.

`tenant_id`, `yacht_id`, `id`, and timestamps are not writable. There is no delete endpoint. Set status to inactive and provide an end date to retain a completed service record.
