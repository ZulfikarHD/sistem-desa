# ADR-004: Profile + password reset via Fortify settings

**Date:** 2026-08-06
**Status:** accepted
**Supersedes:** —

## Context

US-1.4 requires editable contact profile fields with NIK/role immutable by the user, plus password change that verifies the old password. US-1.5 requires forgot-password reset via email token with limited lifetime. The Phase 01 plan mentions “Laravel Breeze,” but the application already uses Laravel Fortify + Livewire settings pages from the starter kit (registration, login, role middleware).

## Decision

1. Extend the existing Livewire profile page with `no_telepon` and `alamat`, and show NIK/role as readonly `#[Locked]` fields.
2. Keep password change on `settings/security` using Fortify-compatible `current_password` validation.
3. Use Fortify `Features::resetPasswords()` (already enabled) for US-1.5 instead of introducing Breeze.
4. Keep token expiry at 60 minutes via `config/auth.php` `passwords.users.expire`.

## Consequences

### Positive

- Reuses the established Fortify + Livewire auth stack; no second auth package.
- Shared settings routes remain role-agnostic (aligned with US-1.3 middleware rule).
- AC for both stories are satisfied with minimal new surface area.

### Negative

- Plan wording (“Breeze”) diverges from implementation naming; docs must call out Fortify explicitly.
- Security page still hosts starter-kit 2FA/passkeys UI outside these story ACs.

### Neutral

- Delete-account and appearance settings remain available but undocumented as Phase 01 deliverables.
