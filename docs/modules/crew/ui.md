# Crew UI

The existing YachtOS visual language remains the source of truth. Increment 4 connects only the roster `/crew`, member detail `/crew/{id}` (the existing demo detail route redirects to the first real member), and create/edit forms `/crew/new` and `/crew/{id}/edit` to the API.

The roster shows name, position, status, contact and service start date; rows open the detail screen. Search works locally over the loaded roster. Empty, loading, and recoverable API states are shown without replacing the application shell. Detail and edit use server data. The create/edit form uses minimal operational fields and displays validation errors beside fields and a safe form-level error. Saves return to the roster/detail and subsequent reload reads persisted API data.

All feature requests use the centralized API client and active yacht UUID from current auth/yacht context as `X-Yacht-Id`. No tenant or yacht ownership value is held as editable form state. Scheduling, leave, documents, payroll and certification screens remain prototypes and are not represented as real data.
