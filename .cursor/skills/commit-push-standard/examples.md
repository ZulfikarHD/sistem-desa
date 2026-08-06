# Commit Message Examples

Contoh commit message menggunakan standar yang diperbarui.

---

## Contoh: Commit Deskriptif dengan Body

### Fitur UI components (multi-file)

```
feat(ui): tambah DataTable, EmptyState, CurrencyDisplay, dan FormField components

Dibutuhkan reusable components untuk halaman CRUD yang akan dibuat 
di sprint berikutnya. Tanpa standar components, setiap developer akan 
buat implementasi sendiri. DataTable harus support pagination, 
filtering, dan responsive card view di mobile untuk UX yang konsisten.

Modified: DataTable (DataTablePagination.vue, DataTableFilter.vue, 
types.ts), EmptyState.vue, DateDisplay.vue, CurrencyDisplay.vue, 
FormField.vue, useCurrency.ts, useOnlineStatus.ts

Refs: US-S0.6, US-S0.7
```

### Layout dan navigation

```
feat(layout): tambah sidebar navigation dan role-based layout untuk admin, guru, dan ortu

Setiap role membutuhkan navigasi yang berbeda sesuai fitur yang 
diakses. Layout harus responsive dengan sidebar di desktop dan bottom 
nav di mobile (khusus ortu). Ini bagian dari sprint 0 setup 
infrastruktur frontend sebelum mulai develop fitur bisnis.

Modified: AppLayout.vue (base layout dengan slot sidebar/content), 
AdminLayout.vue, TeacherLayout.vue, ParentLayout.vue (menu items 
sesuai role), Sidebar.vue (collapse state + active indicator), 
MobileBottomNav.vue (untuk parent di mobile)

Tests: Manual test navigasi di setiap role, responsive behavior di 
viewport 360px dan 1280px
Refs: US-S0.4
```

### CI/CD pipeline

```
feat(ci): tambah GitHub Actions CI/CD pipeline dengan tenant isolation scan

Belum ada automated testing dan deployment pipeline, sementara aplikasi 
multi-tenant butuh jaminan data isolation tidak bocor antar sekolah. 
Manual deployment juga rawan human error terutama saat deploy ke 
production. Pipeline ini otomatis jalankan test dan tenant isolation 
scan sebelum deployment.

Modified: .github/workflows/ci.yml (lint, test, build), 
.github/workflows/deploy.yml (staging dan production deployment), 
scripts/check-tenant-isolation.sh, Dockerfile (multi-stage build)

Tests: Pipeline tested di branch feature (semua step passing), tenant 
isolation scan detect 0 violations
Refs: US-S0.5
```

### Fix bug

```
fix(auth): perbaiki redirect loop saat session expired di halaman dashboard

User yang session-nya expired tetap stuck di halaman dashboard. API 
call gagal dengan 401 tapi frontend tidak handle redirect, jadi user 
terus lihat error tanpa tau harus refresh. Dilaporkan 5 user dalam 
seminggu terakhir, jadi butuh fix segera.

Modified: Axios response interceptor (handle 401), router middleware 
(auto-redirect ke login dengan flash message), session storage 
(simpan intended URL untuk redirect setelah re-login)

Tests: Feature test simulasi expired session dengan assertRedirect, 
manual test dengan session lifetime diperpendek ke 1 menit
```

### Refactor

```
refactor(payment): ekstrak payment logic dari controller ke PaymentService

PaymentController sudah 400+ baris dan sulit di-maintain. Logic payment 
juga perlu reusable untuk fitur booking dan membership yang akan 
dikembangkan sprint depan. Sekarang tidak ada separation of concerns 
antara HTTP layer dan business logic, jadi refactor untuk better 
architecture.

Modified: PaymentService (method process, verify, refund), 
PaymentGatewayInterface (abstraksi Midtrans/Xendit), PaymentController 
(sekarang hanya handle request/response, delegate ke service)

Tests: Semua existing test passing tanpa modifikasi, tambah unit test 
PaymentService (12 test cases)
```

### Database migration

```
feat(akademik): tambah tabel mata pelajaran dan jadwal kelas

Guru membutuhkan manajemen jadwal mengajar dan admin perlu assign mata 
pelajaran ke kelas dan guru. Ini foundation untuk fitur absensi dan 
penilaian di sprint berikutnya, jadi schema harus support relasi yang 
kompleks (guru bisa mengajar banyak mata pelajaran, jadwal tidak boleh 
bentrok).

Modified: Migration (subjects, class_schedules, subject_teacher pivot), 
Model (Subject, ClassSchedule dengan relasi dan factory), Seeder 
(SubjectSeeder dengan mata pelajaran kurikulum merdeka), Enum 
(SubjectCategory: Umum, Muatan Lokal, Ekstrakurikuler)

Breaking: Perlu jalankan migration dengan php artisan migrate
Tests: Feature test CRUD subjects, unit test validasi schedule conflict
Refs: US-S1.3
```

### Bug fix yang butuh migration baru (tetap `fix`, bukan `feat`)

```
fix(pos): perbaiki stok tidak berkurang saat order redemption poin

Order yang dibayar pakai redemption poin loyalty tidak mengurangi stok 
produk karena kolom redemption belum ada di tabel orders, jadi kondisi 
pengecekan payment method selalu fallback ke cash dan skip logic 
pengurangan stok. Butuh migration untuk simpan redemption_id dan 
redemption_amount supaya observer stok bisa detect order jenis ini.

Modified: Migration (add_redemption_fields_to_orders_table), Order 
model (kolom redemption_id, redemption_amount), OrderObserver (fix 
kondisi pengurangan stok)

Tests: Feature test order dengan redemption poin, assert stok berkurang
```

### Redesign UI murni, tanpa fitur baru (`style`, bukan `feat`)

```
style(produk): redesign card produk jadi lebih compact dan konsisten dengan brand

Card produk saat ini terlalu banyak whitespace di mobile, cuma muat 2 
kolom padahal target device rata-rata 5 inch. Ganti spacing, ukuran 
font, dan warna badge stok supaya konsisten dengan palette cokelat/pink 
yang sudah dipakai di halaman lain. Tidak ada perubahan data atau 
interaksi baru, murni visual.

Modified: ProductCard.vue (padding, font-size, badge color), 
tailwind.config (tidak berubah, hanya utility class dipakai ulang)
```

### Restrukturisasi komponen tanpa ubah behavior (`refactor`, bukan `feat`)

```
refactor(checkout): pecah CheckoutPage jadi CheckoutSummary dan CheckoutForm

CheckoutPage.vue sudah 500+ baris nyampur logic form dan summary total, 
sulit dibaca dan sulit ditest terpisah. Tidak ada perubahan behavior 
atau tampilan untuk user, cuma pemisahan komponen supaya lebih mudah 
maintain dan reusable kalau nanti dipakai di halaman lain.

Modified: CheckoutPage.vue (sekarang cuma layout wrapper), 
CheckoutSummary.vue (baru, extract dari CheckoutPage), 
CheckoutForm.vue (baru, extract dari CheckoutPage)

Tests: Existing feature test checkout masih passing tanpa modifikasi
```

### Optimasi performa (`perf`)

```
perf(dashboard): eager load relasi order items untuk hilangkan N+1 query

Dashboard laporan penjualan generate 200+ query terpisah saat load 100 
order karena OrderItem diakses di loop tanpa eager loading. Halaman 
butuh 4-5 detik untuk load, terlalu lambat untuk dipakai kasir di jam 
ramai.

Modified: DashboardController (with('items.product') pada query order), 
OrderResource (tidak berubah, cuma benefit dari eager load)

Tests: Assert query count via DB::listen turun dari 200+ jadi 3
```

### Update dependency (`chore`)

```
chore(deps): update laravel/framework ke v13.2 dan vue ke v3.5.x

Ada security advisory di laravel/framework v13.1 terkait validasi upload 
file, dan vue 3.5 fix beberapa reactivity bug yang berpengaruh ke 
composable useCurrency. Sekaligus update minor dependency lain yang 
tertinggal biar tidak numpuk breaking change nanti.

Modified: composer.json, composer.lock, package.json, pnpm-lock.yaml

Tests: composer ci:check passing, manual smoke test checkout flow
```

### Dokumentasi (`docs`)

```
docs(adr): tambah ADR-030 alasan pnpm sebagai package manager eksklusif

Beberapa kali ada kontributor tidak sengaja jalan npm install yang 
generate package-lock.json dan bikin resolusi dependency beda dari 
pnpm-lock.yaml. Perlu didokumentasikan alasan dan konsekuensinya supaya 
tidak terulang dan jadi referensi saat onboarding.

Modified: docs/dev-docs/decisions/030-pnpm-exclusive-package-manager.md
```

### Revert commit

```
revert: batalkan "feat(promo): tambah diskon otomatis per kategori"

Logic diskon otomatis salah hitung untuk produk yang masuk 2 kategori 
sekaligus, menyebabkan diskon dobel diterapkan ke beberapa transaksi 
kasir pagi ini. Revert dulu sampai ada fix yang tervalidasi, supaya 
transaksi hari ini tidak kena diskon salah lagi.

Refs: revert dari commit a1b2c3d
```

---

## Contoh: Commit Trivial (Tanpa Body)

Hanya untuk perubahan yang BENAR-BENAR trivial:

```
fix(ui): perbaiki typo "pembayran" menjadi "pembayaran"
```

```
style(lint): format ulang file sesuai pint rules
```

```
chore(deps): bump yarn.lock setelah update
```

---

## Anti-Pattern (JANGAN Lakukan)

```
# Terlalu singkat — tidak ada konteks apa yang ditambah
feat(ui): tambah components

# Generic — update apa? kenapa?
update code

# WIP tanpa informasi
wip

# Menjelaskan APA yang sudah jelas dari diff, bukan MENGAPA
feat(user): tambah kolom phone_number di tabel users

# Summary ambigu — base apa? layout apa?
feat(layout): tambah base layout

# Body tidak menjelaskan MENGAPA (cuma repeat summary)
feat(auth): tambah login page

Tambah halaman login untuk user bisa masuk ke aplikasi.

Modified: LoginPage.vue, LoginController.php
(^ Body cuma repeat summary, tidak ada konteks atau alasan)

# Body yang baik seharusnya:
feat(auth): tambah login page

User sekarang harus login dulu sebelum akses dashboard karena ada 
data sensitif siswa. Implementasi login dengan session-based auth dan 
remember me feature untuk UX yang lebih baik.

Modified: LoginPage.vue, LoginController.php, AuthMiddleware
Tests: Feature test login flow, test remember me cookie

# Default ke "feat" padahal ini bug fix (fix tetap "fix" walau butuh
# migration/model baru — file baru bukan alasan untuk pilih feat)
feat(order): tambah kolom redemption_id untuk perbaiki bug stok

# Seharusnya:
fix(order): perbaiki stok tidak berkurang saat order pakai redemption poin

# Default ke "feat" padahal ini cuma redesign visual tanpa fitur baru
feat(ui): redesign halaman produk

# Seharusnya (tergantung ada perubahan struktur komponen atau tidak):
style(produk): redesign card produk jadi lebih compact
# atau
refactor(produk): restrukturisasi ProductPage jadi komponen lebih kecil
```

---

## PR Description Contoh

```markdown
## Ringkasan
- Tambah reusable DataTable component dengan pagination dan responsive card view
- Tambah shared components: EmptyState, CurrencyDisplay, DateDisplay
- Tambah FormField component untuk standarisasi form layout

## Mengapa
Sprint berikutnya akan banyak halaman CRUD (siswa, guru, pembayaran).
Tanpa standar components, setiap halaman akan punya implementasi berbeda
yang menyulitkan maintenance. Components ini juga sudah dioptimasi untuk
budget Android device (Redmi 9-class) yang jadi target utama user parent.

## Perubahan Utama
- DataTable: support server-side pagination, search filter, mobile card layout
- CurrencyDisplay: format Rupiah dengan useCurrency composable
- EmptyState: consistent empty state dengan icon, title, description, CTA
- FormField: wrapper untuk label + input + error + hint

## Testing
- [x] Unit test useCurrency formatting
- [x] Manual test responsive DataTable di 360px viewport
- [ ] Integration test dengan real API data

## Catatan untuk Reviewer
- DataTable pagination menggunakan Inertia preserveState untuk UX
- CurrencyDisplay intentionally tidak pakai Intl.NumberFormat karena
  inconsistent di Android WebView lama
```
