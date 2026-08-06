---
paths:
  - 'app/Livewire/JenisSurat/**'
---

# Jenis Surat

## Jenis surat soft/hard delete and field rules
Jenis surat supports soft delete (Arsipkan), restore (Pulihkan from arsip toggle), and hard delete (Hapus Permanen only from arsip, with confirm modal). persyaratan_dokumen is required; deskripsi is optional/nullable. Unique nama_surat still applies across soft-deleted rows due to DB unique index.
