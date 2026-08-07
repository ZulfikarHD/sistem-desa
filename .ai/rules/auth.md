---
paths:
  - resources/views/welcome.blade.php
  - 'resources/views/layouts/auth/**'
  - 'resources/views/pages/auth/**'
---

# Auth

## Password reset uses Fortify not Breeze
US-1.5: forgot/reset password uses Fortify Features::resetPasswords with expire=60 in config/auth.php. Plan mentions Breeze but this app standardizes on Fortify. Keep Indonesian UI on forgot/reset views.

## Guest pages use branded split auth
Welcome + Fortify auth views use forest/saffron brand tokens from app.css and layouts/auth/split (light mode, no forced dark). Keep Indonesian copy strings asserted by e2e (e.g. Masuk ke akun Anda, Registrasi Akun Warga). APP_NAME is Pelayanan Surat Desa.
