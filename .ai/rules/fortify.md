---
paths:
  - 'app/Actions/Fortify/**'
---

# Fortify

## Registration always forces role warga
CreateNewUser must hardcode role=warga and never read role from request input. After register, App\Http\Responses\RegisterResponse logs the user out and redirects to login (US-1.1). Do not restore Fortify default auto-login redirect to dashboard.
