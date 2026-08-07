---
paths:
  - 'database/seeders/**'
---

# Seeders

## UserSeeder provides fixed test accounts
DatabaseSeeder calls UserSeeder. Fixed accounts: admin@desa.test and warga@desa.test with password `password`, plus 5 factory warga. Use only on local/test DBs — never production.

## JenisSuratSeeder seeds Indonesian letter types
DatabaseSeeder calls UserSeeder then JenisSuratSeeder. JenisSuratSeeder uses updateOrCreate on nama_surat for 14 common desa letter types (domisili, SKTM, usaha, kelahiran, kematian, dll.) with Indonesian persyaratan. Keep KTP and Kartu Keluarga keywords in persyaratan_dokumen so FormPengajuanSurat upload slots detect correctly. Local/test only — never production.

## Demo factory block must stay off production
DatabaseSeeder::seedDemoFactoryData() creates sample pengajuan (mixed statuses) + dokumen/log/notifikasi/surat_terbit via factories, recycling admin@desa.test, warga@desa.test, and seeded jenis surat. Call is marked with comment to disable/comment out in production — local/testing only.
