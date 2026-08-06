---
paths:
  - routes/web.php
---

# Routes

## Persyaratan dokumen excluded from auth
persyaratan-dokumen.index is registered outside the auth+verified group (US-2.3). Do not wrap it in role:warga again. Admin jenis-surat stays inside role:admin.
