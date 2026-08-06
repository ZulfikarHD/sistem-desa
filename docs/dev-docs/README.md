# Developer Documentation

Technical documentation for contributors working on sistem-desa.

## Features

| Feature | Doc |
|---------|-----|
| Citizen Registration (US-1.1) | [features/citizen-registration.md](features/citizen-registration.md) |
| Role-Based Login (US-1.2) | [features/role-based-login.md](features/role-based-login.md) |
| Role-Based Middleware (US-1.3) | [features/role-middleware.md](features/role-middleware.md) |

## API

No public JSON API for auth yet — Fortify uses session-based form POSTs. Endpoint notes live in the feature docs.

## Decisions (ADR)

| ADR | Title |
|-----|-------|
| [001](decisions/001-registration-name-column-and-redirect.md) | Keep `name` column; logout + redirect to login after register |
| [002](decisions/002-role-based-login-redirect.md) | Role-based login redirect via LoginResponse |
| [003](decisions/003-role-middleware-403.md) | Role middleware denies with HTTP 403 |
