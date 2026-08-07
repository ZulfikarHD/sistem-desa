---
paths:
  - 'app/Livewire/JenisSurat/**'
---

# Jenis Surat

## Jenis surat soft/hard delete and field rules
Jenis surat supports soft delete (Arsipkan), restore (Pulihkan from arsip toggle), and hard delete (Hapus Permanen only from arsip, with confirm modal). persyaratan_dokumen is required; deskripsi is optional/nullable. Unique nama_surat still applies across soft-deleted rows due to DB unique index.

## Persyaratan dokumen is public read-only
US-2.2 + US-2.3: PersyaratanDokumen at /persyaratan-dokumen is outside auth middleware. Guests use layouts::public with CTA Daftar/Login untuk Mengajukan; authenticated users use layouts::app without that CTA. Soft-deleted jenis_surat hidden. Do not add pengajuan submit here (Phase 03).

## Persyaratan jenis surat is structured rows
US-9.1/9.2: source of truth is jenis_surat_persyaratan (nama, cara_pemenuhan unggah|bawa_kantor|info, is_wajib for unggah, urutan). FormJenisSurat dedicated create/edit pages edit rows + pratinjau badges; persyaratan_dokumen is generated summary via syncPersyaratan(). Do not reintroduce a free-text textarea as the upload rule source.

## Keyword detection removed from form pengajuan
US-9.1/9.2/9.3 complete: jenis_surat_persyaratan is source of truth for upload rules. FormPengajuanSurat no longer uses keyword detection. Keep admin FormJenisSurat row editor + syncPersyaratan; do not reintroduce free-text as upload rule source.

## Jenis surat create/edit use dedicated pages
List + arsip/delete stay on DataJenisSurat (`jenis-surat.index`). Create/edit (including structured persyaratan rows + pratinjau) live on FormJenisSurat at `jenis-surat.create` and `jenis-surat.edit` (ADR-028). Do not put the form back into a Flux modal on the list page. Force-delete confirmation modal on the list is OK. Soft-deleted records 404 on edit.

## Persyaratan dokumen page shows structured badges
US-9.4: PersyaratanDokumen list+detail render jenis_surat_persyaratan with badgeLabel/badgeColor (not raw persyaratan_dokumen block). Eager-load persyaratan; search also orWhereHas nama. Soft-delete + public access unchanged.
