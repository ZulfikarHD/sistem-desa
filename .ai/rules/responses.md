---
paths:
  - 'app/Http/Responses/**'
---

# Responses

## Login redirects by role
LoginResponse (bound in FortifyServiceProvider) sends warga to route dashboard and admin to dashboard.admin. User::homeRouteName() is the single source for post-login and layout dashboard links. Do not hardcode /dashboard for authenticated users.
