---
paths:
  - 'resources/views/pages/settings/**'
---

# Settings

## Profile edit never mutates NIK or role
US-1.4: profileRules and updateProfileInformation only allow name, no_telepon, alamat, email. NIK and role are #[Locked] readonly display fields. Do not add nik/role to fillable update paths from the profile form.
