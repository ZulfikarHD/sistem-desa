# Developer Documentation

Technical documentation for contributors working on sistem-desa.

## Features

| Feature | Doc |
|---------|-----|
| Citizen Registration (US-1.1) | [features/citizen-registration.md](features/citizen-registration.md) |

## API

No public JSON API for registration yet — Fortify uses session-based form POSTs. Endpoint notes live in the feature doc.

## Decisions (ADR)

| ADR | Title |
|-----|-------|
| [001](decisions/001-registration-name-column-and-redirect.md) | Keep `name` column; logout + redirect to login after register |
