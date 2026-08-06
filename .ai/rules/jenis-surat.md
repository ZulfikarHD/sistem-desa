---
paths:
  - 'app/Livewire/JenisSurat/**'
---

# Jenis Surat

## Jenis surat soft/hard delete and field rules
Jenis surat supports soft delete (Arsipkan), restore (Pulihkan from arsip toggle), and hard delete (Hapus Permanen only from arsip, with confirm modal). persyaratan_dokumen is required; deskripsi is optional/nullable. Unique nama_surat still applies across soft-deleted rows due to DB unique index.

## Persyaratan dokumen is public read-only
US-2.2 + US-2.3: PersyaratanDokumen at /persyaratan-dokumen is outside auth middleware. Guests use layouts::public with CTA Daftar/Login untuk Mengajukan; authenticated users use layouts::app without that CTA. Soft-deleted jenis_surat hidden. Do not add pengajuan submit here (Phase 03).
