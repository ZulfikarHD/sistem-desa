# ADR-005: Public Pages Brand Redesign

**Date:** 2026-08-06
**Status:** accepted
**Supersedes:** —

## Context

Phase 01 auth was functional but still used the stock Laravel welcome page and a forced-dark Fortify auth shell. That UI did not communicate the village letter-service product, and dark-by-default forms are harder for warga awam to read.

## Decision

Redesign welcome, login, and register around a light forest-green / saffron brand:

- Welcome: full-bleed hero with brand-first typography (Fraunces + Instrument Sans).
- Auth: shared `layouts/auth/split` with brand panel + form column (no forced `dark` class).
- Theme tokens live in `resources/css/app.css`; `APP_NAME` defaults to `Pelayanan Surat Desa`.

Keep existing Fortify flows and Indonesian copy strings already covered by Pest/Playwright tests.

## Consequences

### Positive

- Clear product identity on first visit
- Consistent guest UX across welcome and auth
- Light forms improve readability for warga

### Negative

- Auth layout no longer matches Laravel starter dark aesthetic
- Brand colors must be kept in sync when extending dashboard chrome later

### Neutral

- Forgot-password and other auth pages inherit the same split layout automatically
