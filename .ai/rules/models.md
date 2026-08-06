---
paths:
  - app/Models/User.php
---

# Models

## User display name stays column name
Phase 01 plan field nama is stored as Laravel column name. Domain fields nik, no_telepon, alamat, role were added. Do not rename name to nama without a coordinated migration across Fortify, profile, and factories.
