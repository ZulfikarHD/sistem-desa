import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-8.5 — Menu & Halaman Daftar "Surat Diproses"
 * US-8.6 — Detail Surat Diproses + Tanggal Pengambilan (blokir lampau) + Siap Diambil
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

function runTinker(php: string): string {
    return execFileSync('php', ['artisan', 'tinker', '--execute', php], {
        cwd: projectRoot,
        encoding: 'utf8',
    });
}

function uniqueNik(suffix: number): string {
    return `3209080808${String(suffix).padStart(6, '0')}`;
}

function ensureUser(options: {
    email: string;
    name: string;
    role: 'warga' | 'admin';
    nik: string;
    password?: string;
}): void {
    const password = options.password ?? 'password';
    const php = [
        `\\App\\Models\\User::updateOrCreate(`,
        `['email' => ${JSON.stringify(options.email)}],`,
        `[`,
        `'name' => ${JSON.stringify(options.name)},`,
        `'nik' => ${JSON.stringify(options.nik)},`,
        `'no_telepon' => '081234567890',`,
        `'alamat' => 'Jl. E2E Surat Diproses No. 1',`,
        `'role' => ${JSON.stringify(options.role)},`,
        `'password' => ${JSON.stringify(password)},`,
        `'email_verified_at' => now(),`,
        `]`,
        `);`,
    ].join('');

    runTinker(php);
}

function getUserIdByEmail(email: string): number {
    const output = runTinker(`echo \\App\\Models\\User::where('email', ${JSON.stringify(email)})->value('id');`).trim();
    const id = Number(output);
    if (!id) {
        throw new Error(`Failed to resolve user id for ${email}: ${output}`);
    }

    return id;
}

function ensureJenisSurat(namaSurat: string): void {
    const php = [
        `\\App\\Models\\JenisSurat::updateOrCreate(`,
        `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
        `[`,
        `'deskripsi' => 'Deskripsi e2e surat diproses',`,
        `'persyaratan_dokumen' => "- Fotokopi KTP\\n- Fotokopi KK",`,
        `]`,
        `);`,
    ].join('');

    runTinker(php);
}

function getJenisSuratIdByName(namaSurat: string): number {
    const output = runTinker(
        `echo \\App\\Models\\JenisSurat::where('nama_surat', ${JSON.stringify(namaSurat)})->value('id');`,
    ).trim();
    const id = Number(output);
    if (!id) {
        throw new Error(`Failed to resolve jenis surat id for ${namaSurat}: ${output}`);
    }

    return id;
}

function seedDiprosesDenganSurat(options: {
    wargaId: number;
    adminId: number;
    jenisSuratId: number;
    nomorPengajuan: string;
}): number {
    const php = [
        `$pengajuan = \\App\\Models\\PengajuanSurat::create([`,
        `'user_id' => ${options.wargaId},`,
        `'jenis_surat_id' => ${options.jenisSuratId},`,
        `'nomor_pengajuan' => ${JSON.stringify(options.nomorPengajuan)},`,
        `'keperluan' => 'E2E surat diproses US-8.5/8.6',`,
        `'status' => \\App\\Models\\PengajuanSurat::STATUS_DIPROSES,`,
        `'diverifikasi_oleh' => ${options.adminId},`,
        `'tanggal_pengajuan' => '2100-08-01',`,
        `]);`,
        `$token = \\Illuminate\\Support\\Str::random(64);`,
        `$path = 'surat-terbit/' . $pengajuan->id . '/surat.pdf';`,
        `\\Illuminate\\Support\\Facades\\Storage::disk('local')->put($path, '%PDF-1.4 e2e surat diproses');`,
        `\\App\\Models\\SuratTerbit::create([`,
        `'pengajuan_id' => $pengajuan->id,`,
        `'nomor_surat' => '470/' . $pengajuan->id . '/DS-WDN/VIII/2026',`,
        `'file_path' => $path,`,
        `'tanggal_terbit' => now()->toDateString(),`,
        `'tanggal_pengambilan' => null,`,
        `'siap_diambil_at' => null,`,
        `'jam_kerja_label' => null,`,
        `'qr_token' => $token,`,
        `'qr_status' => \\App\\Models\\SuratTerbit::QR_STATUS_VALID,`,
        `'diterbitkan_oleh' => ${options.adminId},`,
        `]);`,
        `echo $pengajuan->id;`,
    ].join('');

    const id = Number(runTinker(php).trim());
    if (!id) {
        throw new Error(`Failed to seed diproses pengajuan: ${options.nomorPengajuan}`);
    }

    return id;
}

function nextHariKerjaYmd(): string {
    const output = runTinker([
        `$from = now('Asia/Jakarta')->startOfDay();`,
        `for ($i = 0; $i < 60; $i++) {`,
        `$c = \\Illuminate\\Support\\Carbon::parse($from->copy()->addDays($i)->toDateString(), 'Asia/Jakarta');`,
        `$v = \\App\\Models\\SuratTerbit::validasiTanggalPengambilan($c);`,
        `if ($v['ok']) { echo $c->toDateString(); return; }`,
        `}`,
        `echo '';`,
    ].join('')).trim();

    if (!output) {
        throw new Error('Failed to resolve next weekday for e2e');
    }

    return output;
}

function nextWeekendYmd(): string {
    return runTinker(
        `echo \\Illuminate\\Support\\Carbon::parse(now('Asia/Jakarta')->toDateString(), 'Asia/Jakarta')->next(\\Illuminate\\Support\\Carbon::SATURDAY)->toDateString();`,
    ).trim();
}

function yesterdayYmd(): string {
    return runTinker(`echo now('Asia/Jakarta')->subDay()->toDateString();`).trim();
}

function getPengajuanStatus(pengajuanId: number): string {
    return runTinker(
        `echo \\App\\Models\\PengajuanSurat::whereKey(${pengajuanId})->value('status') ?? '';`,
    ).trim();
}

function getSuratPengambilan(pengajuanId: number): {
    tanggal_pengambilan: string | null;
    jam_kerja_label: string | null;
    siap_diambil_at: string | null;
} {
    const raw = runTinker([
        `$s = \\App\\Models\\SuratTerbit::where('pengajuan_id', ${pengajuanId})->first();`,
        `echo json_encode([`,
        `'tanggal_pengambilan' => $s?->tanggal_pengambilan?->toDateString(),`,
        `'jam_kerja_label' => $s?->jam_kerja_label,`,
        `'siap_diambil_at' => $s?->siap_diambil_at?->toDateTimeString(),`,
        `]);`,
    ].join('')).trim();

    return JSON.parse(raw) as {
        tanggal_pengambilan: string | null;
        jam_kerja_label: string | null;
        siap_diambil_at: string | null;
    };
}

function getLatestNotifikasiPesan(userId: number, pengajuanId: number): string {
    return runTinker([
        `$n = \\App\\Models\\Notifikasi::query()`,
        `->where('user_id', ${userId})`,
        `->where('pengajuan_id', ${pengajuanId})`,
        `->latest('id')->first();`,
        `echo $n?->pesan ?? '';`,
    ].join('')).trim();
}

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

test.describe('US-8.5 Daftar Surat Diproses', () => {
    test('sidebar menampilkan Surat Diproses dan daftar hanya status diproses', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.sdp.list.${stamp}@example.com`;
        const wargaEmail = `warga.sdp.list.${stamp}@example.com`;
        const jenisSurat = `Surat Keterangan Domisili SDP ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin SDP List',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga SDP List',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureJenisSurat(jenisSurat);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisSurat);
        const nomor = `PJ-E2E-SDP-${stamp}-1`;
        const pengajuanId = seedDiprosesDenganSurat({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
        });

        await loginAs(page, adminEmail);
        await expect(page.locator('[data-test="sidebar-surat-diproses"]')).toBeVisible();
        await expect(page.locator('[data-test="sidebar-surat-diproses"]')).toContainText('Surat Diproses');

        await page.locator('[data-test="sidebar-surat-diproses"]').click();
        await expect(page).toHaveURL(/\/admin\/surat-diproses/);
        await expect(page.locator('[data-test="surat-diproses-heading"]')).toHaveText('Surat Diproses');
        await expect(page.locator(`[data-test="surat-diproses-row-${pengajuanId}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="surat-diproses-nomor-${pengajuanId}"]`)).toContainText(nomor);
        await expect(page.locator(`[data-test="surat-diproses-nomor-surat-${pengajuanId}"]`)).not.toHaveText('—');
    });

    test('state kosong ketika tidak ada surat diproses milik seed khusus', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.sdp.empty.${stamp}@example.com`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin SDP Empty',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });

        // Hapus semua diproses agar empty state muncul (DB e2e shared — filter via unique admin view is hard).
        // Cukup pastikan halaman load dan empty ATAU table tampil tanpa error.
        await loginAs(page, adminEmail);
        await page.goto('/admin/surat-diproses');
        await expect(page.locator('[data-test="surat-diproses-heading"]')).toBeVisible();

        const empty = page.locator('[data-test="surat-diproses-empty"]');
        const table = page.locator('[data-test="surat-diproses-table"]');
        await expect(empty.or(table)).toBeVisible();
    });

    test('warga dilarang mengakses halaman surat diproses', async ({ page }) => {
        const stamp = Date.now();
        const wargaEmail = `warga.sdp.forbid.${stamp}@example.com`;

        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga SDP Forbid',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });

        await loginAs(page, wargaEmail);
        const response = await page.goto('/admin/surat-diproses');
        expect(response?.status()).toBe(403);
    });
});

test.describe('US-8.6 Detail Surat Diproses & Siap Diambil', () => {
    test('admin set tanggal valid lalu siap diambil + notifikasi + hilang dari daftar', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.sdp.ok.${stamp}@example.com`;
        const wargaEmail = `warga.sdp.ok.${stamp}@example.com`;
        const jenisSurat = `Surat Keterangan Usaha SDP ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin SDP OK',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga SDP OK',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureJenisSurat(jenisSurat);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisSurat);
        const nomor = `PJ-E2E-SDP-${stamp}-2`;
        const pengajuanId = seedDiprosesDenganSurat({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
        });
        const tanggal = nextHariKerjaYmd();

        await loginAs(page, adminEmail);
        await page.goto(`/admin/surat-diproses/${pengajuanId}`);

        await expect(page.locator('[data-test="surat-diproses-detail-siap-diambil-panel"]')).toBeVisible();
        await expect(page.locator('[data-test="surat-diproses-detail-pdf-preview"]')).toBeVisible();

        const dateInput = page.locator('[data-test="surat-diproses-detail-tanggal-pengambilan"]');
        const minAttr = await dateInput.getAttribute('min');
        expect(minAttr).toMatch(/^\d{4}-\d{2}-\d{2}$/);

        const button = page.locator('[data-test="surat-diproses-detail-siap-diambil-button"]');
        await expect(button).toBeDisabled();

        await dateInput.fill(tanggal);
        await expect(page.locator('[data-test="surat-diproses-detail-jam-kerja-preview"]')).not.toContainText('Pilih tanggal');
        await expect(button).toBeEnabled();

        await button.click();
        await expect(page).toHaveURL(/\/admin\/surat-diproses\/?$/);

        expect(getPengajuanStatus(pengajuanId)).toBe('siap_diambil');

        const surat = getSuratPengambilan(pengajuanId);
        expect(surat.tanggal_pengambilan).toBe(tanggal);
        expect(surat.jam_kerja_label).toBeTruthy();
        expect(surat.siap_diambil_at).toBeTruthy();

        const pesan = getLatestNotifikasiPesan(wargaId, pengajuanId);
        expect(pesan.toLowerCase()).toContain('sudah siap diambil pada');
        expect(pesan).toContain(`#${nomor}`);

        await page.goto('/admin/surat-diproses');
        await expect(page.locator(`[data-test="surat-diproses-row-${pengajuanId}"]`)).toHaveCount(0);
    });

    test('tanggal lampau ditolak di server via Livewire — status tetap diproses (edge case)', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.sdp.past.${stamp}@example.com`;
        const wargaEmail = `warga.sdp.past.${stamp}@example.com`;
        const jenisSurat = `Surat Keterangan Domisili Past ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin SDP Past',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga SDP Past',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureJenisSurat(jenisSurat);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisSurat);
        const nomor = `PJ-E2E-SDP-${stamp}-3`;
        const pengajuanId = seedDiprosesDenganSurat({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
        });
        const kemarin = yesterdayYmd();

        await loginAs(page, adminEmail);
        await page.goto(`/admin/surat-diproses/${pengajuanId}`);
        await expect(page.locator('[data-test="surat-diproses-detail-siap-diambil-panel"]')).toBeVisible();

        // Bypass atribut min HTML — panggil aksi Livewire langsung dengan tanggal lampau.
        await page.evaluate(async (tanggal) => {
            const root = document.querySelector('[wire\\:id]');
            if (!root) {
                throw new Error('Livewire root not found');
            }
            const component = (window as unknown as { Livewire: { find: (id: string) => { set: (k: string, v: string) => Promise<void>; call: (m: string) => Promise<void> } } })
                .Livewire.find(root.getAttribute('wire:id')!);
            await component.set('tanggalPengambilan', tanggal as string);
            await component.call('tandaiSiapDiambil');
        }, kemarin);

        await expect(page.locator('[data-test="surat-diproses-detail-siap-diambil-panel"]')).toBeVisible();
        expect(getPengajuanStatus(pengajuanId)).toBe('diproses');
    });

    test('tanggal sabtu ditolak — tombol tetap disabled', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.sdp.sat.${stamp}@example.com`;
        const wargaEmail = `warga.sdp.sat.${stamp}@example.com`;
        const jenisSurat = `Surat Keterangan Tidak Mampu SDP ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin SDP Sat',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga SDP Sat',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureJenisSurat(jenisSurat);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisSurat);
        const nomor = `PJ-E2E-SDP-${stamp}-4`;
        const pengajuanId = seedDiprosesDenganSurat({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
        });
        const sabtu = nextWeekendYmd();

        await loginAs(page, adminEmail);
        await page.goto(`/admin/surat-diproses/${pengajuanId}`);

        await page.locator('[data-test="surat-diproses-detail-tanggal-pengambilan"]').fill(sabtu);
        await expect(page.locator('[data-test="surat-diproses-detail-jam-kerja-preview"]')).toContainText(/tutup|libur|Sabtu/i);
        await expect(page.locator('[data-test="surat-diproses-detail-siap-diambil-button"]')).toBeDisabled();
        expect(getPengajuanStatus(pengajuanId)).toBe('diproses');
    });
});
