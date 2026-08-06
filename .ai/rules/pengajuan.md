---
paths:
  - 'app/Livewire/Pengajuan/**'
---

# Pengajuan

## pengajuan_surat table naming
Phase 03 uses table `pengajuan_surat` (singular). Model PengajuanSurat sets protected $table. Route pengajuan-surat.create is warga-only under role:warga.

## dokumen_persyaratan (US-3.2)
Table `dokumen_persyaratan` (singular). Model DokumenPersyaratan. jenis_dokumen values: KTP, KK. Files stored on private `local` disk under `pengajuan-dokumen/{pengajuan_id}/`. Required upload slots detected from `jenis_surat.persyaratan_dokumen` text (KTP / KK / Kartu Keluarga keywords). US-3.3 blocks submit when required docs missing — `rules()` sets `required` on dokumenKtp/dokumenKk when detected in requiredDokumenTypes.

## US-3.4 ajukan ulang + riwayat routes
Riwayat at route pengajuan-surat.riwayat (/riwayat-pengajuan). Resubmit at pengajuan-surat.resubmit (/pengajuan-surat/ajukan-ulang/{pengajuan}) — mount pre-fills ditolak pengajuan; owner+status ditolak only. Nomor generator uses max numeric suffix (not string orderByDesc) to avoid collision after 9999.

## US-7.5 riwayat shows pickup date and hours
RiwayatPengajuan eager-loads suratTerbit tanggal_pengambilan + jam_kerja_label and shows a Pengambilan column for warga. Do not confuse with US-7.6 detail page display of the same fields.

## US-7.6 unduh on riwayat and detail pickup display
Riwayat shows Unduh Surat button when dapatUnduhSurat(). DetailPengajuanWarga loads suratTerbit and shows tanggal_pengambilan + jam_kerja_label when set, plus Unduh and Cetak buttons for allowed statuses.
