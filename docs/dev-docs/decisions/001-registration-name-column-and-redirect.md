# ADR-001: Keep `name` column and redirect to login after registration

**Date:** 2026-08-06
**Status:** accepted
**Supersedes:** —

## Context

US-1.1 data model in the scrum plan lists a `nama` column and requires redirect to the login page after successful registration. The Livewire starter kit already uses Laravel’s conventional `name` column across the User model, factories, profile settings, and Fortify. Fortify’s default registration response also logs the user in and sends them to `fortify.home` (`/dashboard`).

## Decision

1. Keep the database column `name` (UI label: **Nama**) instead of renaming to `nama`.
2. Add domain fields exactly as planned: `nik`, `no_telepon`, `alamat`, `role`.
3. Bind a custom `RegisterResponse` that logs the user out, invalidates the session, and redirects to the named `login` route with a success flash.

## Consequences

### Positive

- No breaking rename across Fortify/profile/initials helpers.
- Acceptance criterion “redirect to login after register” is met without forking Fortify’s controller.
- Domain fields stay aligned with the Phase 01 data model.

### Negative

- Schema field name diverges from the literal scrum plan (`nama` → `name`).
- Brief authenticated moment exists between Fortify’s `login()` and our `RegisterResponse` logout (mitigated by immediate logout + session invalidate).

### Neutral

- Future stories (US-1.2 role-based dashboards) can rely on the `role` column without further schema changes for registration.
