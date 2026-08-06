---
paths:
  - 'resources/views/pages/auth/**'
---

# Auth

## Password reset uses Fortify not Breeze
US-1.5: forgot/reset password uses Fortify Features::resetPasswords with expire=60 in config/auth.php. Plan mentions Breeze but this app standardizes on Fortify. Keep Indonesian UI on forgot/reset views.
