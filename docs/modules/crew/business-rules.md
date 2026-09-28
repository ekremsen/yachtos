# Crew Business Rules

- CrewMember describes an operational person aboard a yacht; it is not a login identity.
- YachtMembership describes permission for an authenticated User to operate a yacht; it is not a crew roster entry.
- Every CrewMember belongs to exactly one tenant and yacht. Both are assigned from resolved server contexts and cannot be changed through API input.
- Reads and writes are scoped to the current authorized YachtContext. Other-yacht UUIDs return the same not-found response as unknown UUIDs.
- Names are required. Position, email, phone, nationality and service dates may be incomplete.
- Email must be a valid address when supplied; phone is bounded text; nationality is a two-letter country code when supplied.
- Status is `active` or `inactive`. Ending service uses inactive status and an end date, retaining the record.
- An end date must follow the start date when both are present.
- Hard deletion, credentials, payroll, HR compliance, certifications and sensitive employee data are outside this increment.
