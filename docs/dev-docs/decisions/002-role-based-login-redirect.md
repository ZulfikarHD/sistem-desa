# ADR-002: Role-based login redirect via LoginResponse

**Date:** 2026-08-06
**Status:** accepted
**Supersedes:** —

## Context

US-1.2 requires post-login redirects to **Dashboard Warga** (`role = warga`) or **Dashboard Admin** (`role = admin`). Fortify’s `fortify.home` is a single URI (`/dashboard`), which cannot express two destinations. Middleware that blocks cross-role access is scheduled for US-1.3.

## Decision

1. Bind a custom `App\Http\Responses\LoginResponse` implementing Fortify’s `LoginResponse` contract.
2. Add `User::homeRouteName()` returning `dashboard` (warga) or `dashboard.admin` (admin).
3. Register both routes under `auth` (+ `verified`): `/dashboard` and `/admin/dashboard`.
4. Configure `bootstrap/app.php` `redirectUsersTo` with the same role-aware target so authenticated guests visiting `/login` land on the correct dashboard.
5. Keep the warga route name as `dashboard` (not `dashboard.warga`) to avoid breaking existing starter-kit links and tests.

## Consequences

### Positive

- Acceptance criteria for role-based redirect and logout on both dashboards are met without forking Fortify controllers.
- Single helper (`homeRouteName`) keeps layout links consistent with login redirects.

### Negative

- ~~Until US-1.3 middleware lands, a logged-in admin can still manually open `/dashboard` (and vice versa) if they know the URL.~~ **Resolved by US-1.3 / ADR-003** (`role` middleware → 403).
- `fortify.home` remains `/dashboard` as a fallback for non-login Fortify flows (e.g. email verification defaults).

### Neutral

- JSON login responses include a `redirect` key for XHR/passkey clients that prefer client-side navigation.
