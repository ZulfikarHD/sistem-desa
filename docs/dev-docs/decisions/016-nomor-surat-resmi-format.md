# ADR-016: Official Nomor Surat Format and Year Sequence (US-7.3)

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** Partial nomor stub noted in ADR-015 as refined by US-7.3

## Context

US-7.2 already needed an official number on the PDF. US-7.3 owns the numbering convention: village administrative format, uniqueness, per-year sequence, separation from `nomor_pengajuan`, and printing on the PDF. The plan gives the example `470/{urut}/DS-WDN/{bulan romawi}/{tahun}` without specifying zero-padding, manual override, or per-jenis sequences. Rekap display of `nomor_surat` is owned by US-7.7.

## Decision

1. Adopt format `{kode_klasifikasi}/{urut}/{kode_desa}/{bulanRomawi}/{tahun}` with defaults `470` and `DS-WDN` from `config/desa.php` / `.env`.
2. Sequence by calendar year of `tanggal_terbit`; roman month is part of the string, not a separate sequence key.
3. Do not zero-pad `{urut}`.
4. Allocate under year-scoped `Cache::lock` + `DB::transaction` + `lockForUpdate`, with a unique DB column as final guard.
5. Keep generation on `SuratTerbit` (no service class); no admin UI to edit nomor; rekap columns stay US-7.7.

## Consequences

### Positive

- Matches village administrative example from the scrum plan.
- Concurrent approves remain safe without a separate numbering service.
- Clear split from pengajuan tracking numbers.

### Negative

- Changing `kode_klasifikasi` mid-year can make older rows invisible to the regex max scan (unique index still prevents exact duplicates).
- DomPDF binary may not store slash-separated strings as plain text; template HTML is the authoritative print proof.

### Neutral

- Config overrides allow other desa codes without code changes.
- US-7.7 will surface the same stored `nomor_surat` in admin rekap.
