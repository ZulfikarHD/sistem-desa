---
paths:
  - 'app/Livewire/JenisSurat/**'
---

# Jenis Surat

## Jenis surat soft/hard delete and field rules
Jenis surat supports soft delete (Arsipkan), restore (Pulihkan from arsip toggle), and hard delete (Hapus Permanen only from arsip, with confirm modal). persyaratan_dokumen is required; deskripsi is optional/nullable. Unique nama_surat still applies across soft-deleted rows due to DB unique index.

## Warga persyaratan is read-only role:warga
US-2.2 PersyaratanDokumen is auth+verified+role:warga at /persyaratan-dokumen. Read-only list+detail modal; soft-deleted jenis_surat hidden. Do not add public guest access here (US-2.3) or pengajuan submit (Phase 03).
