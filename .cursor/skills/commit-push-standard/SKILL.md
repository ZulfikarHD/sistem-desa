---
name: commit-push-standard
description: Generate standardized commit messages and PR descriptions using Conventional Commits format with Indonesian language. Use when committing code, creating pull requests, writing commit messages, or when the user mentions commit, push, PR, or pull request.
---

# Commit & Push Standard

Skill untuk menulis commit message yang jelas, informatif, dan konsisten.
Format Conventional Commits, bahasa Indonesia. Setiap commit HARUS menjawab: **apa yang berubah, mengapa, dan apa dampaknya**.

## Workflow

```
Diminta commit/push?
│
├─► Run: git status + git diff + git log (parallel)
├─► Analisis SEMUA perubahan (staged + unstaged)
├─► Tentukan type dari INTENT perubahan, pakai decision tree
│   (JANGAN default ke "feat" — lihat "Cara Menentukan Type")
├─► Tulis commit message (SELALU sertakan body)
├─► Stage file relevan (cek tidak ada .env/secrets)
├─► Commit dengan HEREDOC format
├─► Push ke remote
└─► Verifikasi dengan git status
```

## Prinsip Utama

1. **Selalu jelaskan MENGAPA** — diff sudah menunjukkan APA, commit message harus menjelaskan alasan dan konteks
2. **Summary harus cukup deskriptif** — pembaca harus paham garis besar tanpa buka diff
3. **Body WAJIB** — kecuali perubahan benar-benar trivial (typo 1 kata, format otomatis pint)
4. **Sebutkan file/komponen penting** — beri gambaran scope perubahan di body
5. **Type ditentukan dari INTENT, bukan dari jumlah file/baris** — "ada file baru" atau "banyak baris berubah" BUKAN alasan untuk memilih `feat`. Lihat decision tree di bawah.

## Cara Menentukan Type (WAJIB sebelum menulis commit)

Type paling sering salah dipilih adalah `feat` dipakai untuk semua perubahan,
padahal Conventional Commits spec (conventionalcommits.org) hanya mewajibkan
`feat` untuk fitur/kapabilitas baru dan `fix` untuk bug fix — tipe lain
mendeskripsikan intent yang berbeda sama sekali. Jawab pertanyaan berikut
**berurutan**, berhenti begitu ketemu jawaban "ya":

```
1. Apakah ini revert dari commit sebelumnya?
   → YA: revert

2. Apakah ini memperbaiki behavior yang SALAH/rusak/bug
   (sebelumnya tidak sesuai ekspektasi user atau sistem)?
   → YA: fix
   (tetap "fix" walau fix-nya butuh file baru, migration baru, dsb —
    yang menentukan adalah "ini benerin sesuatu yang rusak", bukan
    "ada penambahan kode")

3. Apakah ini kapabilitas/fitur yang BELUM PERNAH ADA sebelumnya
   (user/API bisa melakukan sesuatu yang sebelumnya tidak bisa)?
   → YA: feat

4. Apakah ini HANYA mengubah dependency, tooling, build config, atau
   task rutin yang tidak menyentuh production code/behavior?
   → YA: chore (dependency/tooling umum) / build (build system, versi
      package) / ci (workflow CI/CD)

5. Apakah ini HANYA menambah/menulis dokumentasi (README, komentar,
   ADR, docs/)?
   → YA: docs

6. Apakah ini HANYA formatting/lint/whitespace/redesign visual TANPA
   mengubah struktur kode atau menambah kapabilitas baru
   (ganti warna, spacing, className Tailwind, hasil `pint`/`prettier`)?
   → YA: style

7. Apakah ini restrukturisasi kode/komponen (rename, ekstrak service,
   ubah struktur folder, redesign markup/komponen) TANPA mengubah
   behavior yang terlihat user dan TANPA menambah fitur baru?
   → YA: refactor

8. Apakah ini HANYA optimasi performa (query, cache, bundle size)
   tanpa mengubah behavior fungsional?
   → YA: perf

9. Apakah ini HANYA menambah/memperbaiki test, tanpa mengubah
   production code?
   → YA: test

10. Tidak ada yang cocok?
    → chore (catch-all terakhir, bukan default pertama)
```

**Aturan penting:**

- **"Redesign UI" bukan otomatis `feat`.** Kalau cuma reskin/ubah tampilan
  (warna, layout, spacing, komponen visual) tanpa menambah kapabilitas baru
  → `style` (kalau murni cosmetic) atau `refactor` (kalau struktur komponen
  ikut berubah). Baru jadi `feat` kalau ada fungsi/interaksi baru yang
  ditambahkan bersamaan.
- **Bug fix yang butuh file baru tetap `fix`, bukan `feat`.** Menambah
  migration, model, atau service untuk MEMPERBAIKI bug bukan berarti itu
  fitur baru.
- **Kalau diff berisi 2+ intent yang jelas berbeda** (misal: fix bug DAN
  tambah fitur baru DAN update dependency), **pisah jadi beberapa commit**
  jika memungkinkan (ini juga rekomendasi resmi conventionalcommits.org).
  Kalau tidak memungkinkan untuk dipisah (misal user minta 1 commit),
  pilih type dari perubahan yang paling DOMINAN/signifikan untuk end
  user/API, lalu sebutkan intent lain di body.
- **Jangan pilih type dari nama file yang berubah.** Migration baru ≠
  otomatis `feat`. Component baru ≠ otomatis `feat`. Cek apakah itu
  memperbaiki sesuatu, menambah kapabilitas baru, atau sekadar
  restrukturisasi.

## Commit Message Format

### Summary Line

```
<type>(<scope>): <ringkasan deskriptif present tense>
```

| Aturan | Detail |
|--------|--------|
| Panjang | Maksimal **100 karakter** (bukan 50 — deskriptif lebih penting) |
| Tense | Present tense: "tambah", "perbaiki", "refactor" |
| Huruf | Huruf kecil setelah colon |
| Titik | Tanpa titik di akhir |
| Bahasa | Indonesia, istilah teknis boleh English |

### Body (WAJIB kecuali trivial)

```
<type>(<scope>): <ringkasan deskriptif>

<Paragraph 1-2: Jelaskan MENGAPA perubahan ini diperlukan>
Tulis natural prose yang menjelaskan konteks bisnis/teknis, masalah yang 
diselesaikan, atau kebutuhan fitur. Fokus pada alasan, bukan apa yang 
berubah (diff sudah tunjukkan itu).

Modified: <list file/komponen utama yang berubah>

Breaking: <jika ada breaking changes, jelaskan dampaknya>
Tests: <test yang ditambah/dijalankan>
Refs: #<ticket-number>
```

**Contoh struktur natural:**

```
feat(auth): tambah JWT authentication dengan refresh token support

Mobile app butuh stateless auth karena session-based auth tidak 
reliable di React Native. Cookie storage bermasalah, jadi kita migrate 
ke JWT dengan 24h expiration dan refresh token support untuk user 
experience yang lebih baik.

Modified: LoginController, TokenService, AuthMiddleware, config/jwt.php

Breaking: Endpoint /api/auth/login sekarang return JWT token, bukan 
session. Client harus migrate ke token-based auth.
Tests: Feature test login flow, unit test TokenService validation
Refs: #123
```

### Body Rules

- **Wrap di 72 karakter per baris** (untuk terminal readability)
- **Bahasa Indonesia**, istilah teknis boleh English (controller, service, migration, component)
- **Paragraf pertama WAJIB jelaskan MENGAPA** — ini yang paling penting untuk debugging
- **Modified:** berisi list file/komponen — cukup nama file/class, tidak perlu detail implementasi
- **Breaking/Tests/Refs:** optional, inline di akhir body jika ada

## Tipe Commit

Referensi lengkap semua type (bukan urutan prioritas — lihat decision tree
di atas untuk menentukan type yang tepat):

| Type | Penggunaan | Contoh Scope |
|------|-----------|--------------|
| `fix` | Memperbaiki bug/behavior yang salah | validation, ui, api, auth |
| `feat` | Kapabilitas/fitur yang belum pernah ada | auth, booking, payment, ui |
| `refactor` | Restrukturisasi kode/komponen tanpa ubah behavior | service, model, ui |
| `style` | Formatting, redesign visual murni, tanpa ubah logic | lint, format, ui |
| `perf` | Optimasi performa tanpa ubah behavior fungsional | query, cache, bundle |
| `test` | Penambahan/update test, tanpa ubah production code | unit, feature |
| `docs` | Dokumentasi saja (README, ADR, komentar) | readme, adr, comments |
| `build` | Build tooling, dependency, versi package | deps, vite, composer |
| `ci` | Konfigurasi CI/CD (workflow, pipeline) | github-actions, ci |
| `revert` | Membatalkan commit sebelumnya | — |
| `chore` | Task rutin lain yang tidak masuk kategori di atas | config, gitignore |

**Catatan bias yang harus dihindari:** jangan pilih `feat` hanya karena
scope-nya `ui`/`layout` atau karena ada file baru — banyak perubahan `ui`
sebenarnya `fix` (bug visual) atau `style`/`refactor` (redesign tanpa fitur
baru).

## Kapan Body Boleh Dilewati? (SANGAT JARANG)

Body hanya boleh kosong untuk perubahan yang **benar-benar trivial**:

```
TANPA body (trivial):
├─► Fix typo 1 kata
├─► Auto-format pint/prettier (tanpa logic change)
└─► Update version number di package.json

DENGAN body (SEMUA sisanya):
├─► Tambah file baru (apapun) ──────────► WAJIB body
├─► Ubah logic di file manapun ─────────► WAJIB body
├─► Tambah/ubah component ──────────────► WAJIB body
├─► Perubahan multi-file ───────────────► WAJIB body
├─► Fix bug (apapun) ──────────────────► WAJIB body
├─► Perubahan database/API ─────────────► WAJIB body
├─► Config/environment change ──────────► WAJIB body
└─► Apapun yang butuh > 5 detik dipahami ► WAJIB body
```

## Menulis Summary yang Baik

Summary harus membuat pembaca paham **apa yang terjadi** tanpa buka diff.

| Buruk (terlalu singkat) | Baik (deskriptif) |
|------------------------|-------------------|
| `feat(ui): tambah components` | `feat(ui): tambah DataTable, EmptyState, dan FormField components` |
| `fix(auth): perbaiki login` | `fix(auth): perbaiki redirect loop saat session expired` |
| `feat(layout): tambah layout` | `feat(layout): tambah sidebar navigation dan role-based layout` |
| `refactor(payment): refactor service` | `refactor(payment): ekstrak payment logic ke PaymentService` |
| `chore(deps): update deps` | `chore(deps): update laravel ke v12.1 dan vue ke v3.5` |
| `feat(ci): tambah pipeline` | `feat(ci): tambah GitHub Actions CI/CD dengan tenant isolation scan` |

## PR Description Format

```markdown
## Ringkasan
<1-3 poin penjelasan perubahan utama>

## Mengapa
<konteks bisnis/teknis mengapa perubahan ini diperlukan>

## Perubahan Utama
- <list perubahan signifikan dengan file/komponen>

## Testing
- [ ] <langkah testing yang sudah dilakukan>
- [ ] <hal yang perlu di-test oleh reviewer>

## Screenshot (jika ada perubahan UI)

## Catatan untuk Reviewer
<hal penting yang perlu diperhatikan>
```

## Checklist Sebelum Commit

- [ ] Summary deskriptif dan under 100 chars
- [ ] Body menjelaskan MENGAPA (kecuali trivial)
- [ ] Body menyebutkan komponen/file utama yang berubah
- [ ] Type sudah dicek lewat decision tree — BUKAN asumsi/default `feat`
- [ ] Kalau diff punya 2+ intent berbeda, sudah dipertimbangkan untuk dipisah commit
- [ ] Tidak ada file rahasia (.env, credentials)
- [ ] Referensi ticket jika ada

## Larangan

- JANGAN commit file .env, credentials, atau secrets
- JANGAN ubah git config
- JANGAN force push ke main/master
- JANGAN skip hooks (--no-verify)
- JANGAN commit --amend kecuali commit terakhir belum di-push
- JANGAN tulis summary generic: "fix stuff", "update", "wip", "tambah components"
- JANGAN buat summary yang butuh buka diff untuk dipahami
- JANGAN lewati body untuk perubahan non-trivial
- JANGAN default ke `feat` — tentukan type dari INTENT perubahan pakai decision tree
  di atas, bukan karena "ada file baru" atau "banyak baris berubah"
- JANGAN labeli redesign UI murni (warna/spacing/layout, tanpa fitur baru) sebagai
  `feat` — itu `style` atau `refactor`
- JANGAN labeli bug fix sebagai `feat` hanya karena fix-nya butuh file/migration baru

## Contoh

Lihat [examples.md](examples.md) untuk contoh lengkap.
