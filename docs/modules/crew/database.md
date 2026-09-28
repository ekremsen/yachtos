# Crew Database Design

## `crew_members`

One row represents one operational person serving aboard one yacht. It is not an authenticated User record or a YachtMembership.

| Column | Meaning |
|---|---|
| `id` | UUID primary key |
| `tenant_id`, `yacht_id` | Immutable tenant and yacht ownership |
| `first_name`, `last_name` | Required name |
| `position` | Optional onboard role/title |
| `email`, `phone`, `nationality` | Optional contact/profile details |
| `status` | `active` or `inactive`; defaults to active |
| `start_date`, `end_date` | Optional service dates; end must follow start |
| timestamps | Creation and update times |

## Boundary and retention

`(id, tenant_id)` is a unique yacht identity. Composite foreign key `(yacht_id, tenant_id)` references `(yachts.id, yachts.tenant_id)` with restricted update/delete. A record therefore cannot reference a yacht in another tenant, even if application validation is bypassed. Yacht ownership is immutable and deletions are prohibited at the model boundary. Inactive status and end date preserve history.

No `user_id`, credentials, salary, financial, medical or identity-document fields are stored. Certification records and a fuller employment history are deferred.
