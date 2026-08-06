# ADR-003: Role middleware denies with HTTP 403

**Date:** 2026-08-06
**Status:** accepted
**Supersedes:** —

## Context

US-1.3 requires role-based route protection. The acceptance criteria allow either an HTTP **403** page or a redirect to the user’s role dashboard. Cross-role URL access was previously possible after login (noted in ADR-002).

## Decision

1. Introduce `App\Http\Middleware\EnsureUserHasRole`, aliased as `role` in `bootstrap/app.php`.
2. Apply `role:warga` to `/dashboard` and `role:admin` to the `/admin` route group (starting with `dashboard.admin`).
3. On role mismatch (or missing user), **abort(403)** — not redirect.
4. Keep settings routes under `auth` without role middleware (shared by warga and admin).
5. Do not stub Phase 02/04/06 admin feature routes in this story; attach them to the `role:admin` group when those phases land.

## Consequences

### Positive

- Authorization failures are explicit and testable (`assertForbidden` / HTTP 403 in Playwright).
- Future admin routes can join one middleware group without inventing a second pattern.
- ADR-002 gap (cross-role URL access) is closed for existing dashboards.

### Negative

- Users who mistype a URL see a 403 page instead of a soft redirect to their home dashboard (slightly less guided UX).

### Neutral

- Guest handling remains Laravel `auth` middleware (redirect to login), independent of the role middleware.
