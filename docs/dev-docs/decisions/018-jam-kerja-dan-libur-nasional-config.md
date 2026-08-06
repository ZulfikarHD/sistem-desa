# ADR-018: Jam Kerja Labels + Libur Nasional via Config (Reject Invalid Dates)

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** —

## Context

US-7.5 requires pickup scheduling with Indonesian government office hours (Mon–Thu 08.00–16.00 WIB, Fri 08.00–16.30 WIB) and closed weekends / national holidays. The plan allows either **reject** or **warn** for closed days. There is no holiday API in the stack, and adding a package needs approval.

## Decision

1. Store weekday jam labels in `config/desa.jam_kerja` and persist the selected label on `surat_terbit.jam_kerja_label`.
2. Maintain `config/desa.libur_nasional` as an explicit YYYY-MM-DD list (Asia/Jakarta).
3. **Reject** invalid dates (past, weekend, holiday) server-side via `SuratTerbit::validasiTanggalPengambilan`; keep the UI button disabled until valid.
4. Put mark-ready logic on `SuratTerbit::tandaiSiapDiambil` (same pattern as `scanUntukPengambilan`), triggered from `DetailPengajuanVerifikasi`.

## Consequences

### Positive

- Matches AC without a free time picker.
- Server enforcement cannot be bypassed by enabling a disabled button client-side.
- Config is editable per year without code changes.

### Negative

- Holiday list must be updated manually each year / when SKB changes.
- Approximate religious holiday dates may need correction.

### Neutral

- Detail warga tanggal/jam still owned by US-7.6; rekap columns by US-7.7.
