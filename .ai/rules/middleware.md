---
paths:
  - 'app/Http/Middleware/**'
---

# Middleware

## Role middleware returns 403
US-1.3: alias `role` maps to EnsureUserHasRole. Use role:warga on warga-only routes and role:admin (group under prefix admin) for admin routes. Denied access aborts 403 (not redirect). Settings/profile stay shared without role middleware. Future Phase 02/04/06 admin pages must join the role:admin group — do not create stubs in US-1.3.
