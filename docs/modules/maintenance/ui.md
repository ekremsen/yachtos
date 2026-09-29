# Maintenance UI

The existing YachtOS layout and visual language remain unchanged. Live screens in this increment:

- `/maintenance`: API-backed operational overview with open, overdue, and due-soon counts plus upcoming work.
- `/maintenance/work-orders`: real maintenance list with priority, status, due date, overdue indicator and assignee names.
- `/maintenance/work-orders/{id}`: persisted detail with edit, status, and completion actions.
- `/maintenance/work-orders/new` and `/maintenance/work-orders/{id}/edit`: create/edit form for title, description, type, due date, priority, status and one or more current-yacht CrewMembers.

The assignee control allows multiple CrewMembers. No tenant/yacht ownership field is shown. Requests use the centralized API client and current active yacht header. Loading, true empty, validation and recoverable API errors are visible; API failure never falls back to maintenance mock rows. Search/status/priority filters apply to the fetched roster.

Plans, work-hour meters, fault records, and any attachment, cost, recurrence, or generic Task interface remain prototype/deferred routes and are not represented as operational data. Dashboard-level upcoming-maintenance metrics remain unchanged in this increment.
