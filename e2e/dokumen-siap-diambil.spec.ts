import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-7.5 — Tandai Dokumen Siap Diambil (Admin) + Notifikasi Warga
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

function runTinker(php: string): string {
    return execFileSync('php', ['artisan', 'tinker', '--execute', php], {
        cwd: projectRoot,
        encoding: 'utf8',
    });
}

function uniqueNik(suffix: number): string {
    return `3209090909${String(suffix).padStart(6, '0')}`;
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
        `'alamat' => 'Jl. E2E Siap Diambil No. 1',`,
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
        `'deskripsi' => 'Deskripsi e2e siap diambil',`,
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

/**
 * Seed pengajuan diproses + surat_terbit PDF (siap untuk US-7.5 UI).
 */
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
        `'keperluan' => 'E2E tandai dokumen siap diambil',`,
        `'status' => \\App\\Models\\PengajuanSurat::STATUS_DIPROSES,`,
        `'diverifikasi_oleh' => ${options.adminId},`,
        `'tanggal_pengajuan' => '2100-08-01',`,
        `]);`,
        `$token = \\Illuminate\\Support\\Str::random(64);`,
        `$path = 'surat-terbit/' . $pengajuan->id . '/surat.pdf';`,
        `\\Illuminate\\Support\\Facades\\Storage::disk('local')->put($path, '%PDF-1.4 e2e siap diambil');`,
        `\\App\\Models\\SuratTerbit::create([`,
        `'pengajuan_id' => $pengajuan->id,`,
        `'nomor_surat' => '470/' . $pengajuan->id . '/DS-WDN/VIII/2026',`,
        `'file_path' => $path,`,
        `'tanggal_terbit' => now()->toDateString(),`,
        `'tanggal_pengambilan' => null,`,
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

function getPengajuanStatus(pengajuanId: number): string {
    return runTinker(
        `echo \\App\\Models\\PengajuanSurat::whereKey(${pengajuanId})->value('status') ?? '';`,
    ).trim();
}

function getSuratPengambilan(pengajuanId: number): {
    tanggal_pengambilan: string | null;
    jam_kerja_label: string | null;
} {
    const raw = runTinker([
        `$s = \\App\\Models\\SuratTerbit::where('pengajuan_id', ${pengajuanId})->first();`,
        `echo json_encode([`,
        `'tanggal_pengambilan' => $s?->tanggal_pengambilan?->toDateString(),`,
        `'jam_kerja_label' => $s?->jam_kerja_label,`,
        `]);`,
    ].join('')).trim();

    return JSON.parse(raw) as {
        tanggal_pengambilan: string | null;
        jam_kerja_label: string | null;
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

test.describe('US-7.5 Dokumen Siap Diambil', () => {
    test('admin pilih tanggal valid lalu tandai dokumen siap diambil + notifikasi', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.siap.ok.${stamp}@example.com`;
        const wargaEmail = `warga.siap.ok.${stamp}@example.com`;
        const jenisSurat = `Surat Keterangan Domisili Siap ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin Siap OK',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga Siap OK',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureJenisSurat(jenisSurat);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisSurat);
        const nomor = `PJ-E2E-SIAP-${stamp}-1`;
        const pengajuanId = seedDiprosesDenganSurat({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
        });
        const tanggal = nextHariKerjaYmd();

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);

        await expect(page.locator('[data-test="verifikasi-detail-siap-diambil-panel"]')).toBeVisible();
        const button = page.locator('[data-test="verifikasi-detail-siap-diambil-button"]');
        await expect(button).toBeDisabled();

        await page.locator('[data-test="verifikasi-detail-tanggal-pengambilan"]').fill(tanggal);
        await expect(page.locator('[data-test="verifikasi-detail-jam-kerja-preview"]')).not.toContainText('Pilih tanggal');
        await expect(button).toBeEnabled();

        await button.click();
        await expect(page).toHaveURL(/\/admin\/verifikasi/);

        expect(getPengajuanStatus(pengajuanId)).toBe('siap_diambil');

        const surat = getSuratPengambilan(pengajuanId);
        expect(surat.tanggal_pengambilan).toBe(tanggal);
        expect(surat.jam_kerja_label).toBeTruthy();

        const pesan = getLatestNotifikasiPesan(wargaId, pengajuanId);
        expect(pesan.toLowerCase()).toContain('siap diambil');
        expect(pesan.toLowerCase()).toContain('jam kerja');
        expect(pesan.toLowerCase()).toContain('tanggal pengambilan');
    });

    test('tanggal sabtu ditolak — tombol tetap disabled / status tidak berubah', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.siap.sat.${stamp}@example.com`;
        const wargaEmail = `warga.siap.sat.${stamp}@example.com`;
        const jenisSurat = `Surat Keterangan Usaha Siap ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin Siap Sat',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga Siap Sat',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureJenisSurat(jenisSurat);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisSurat);
        const nomor = `PJ-E2E-SIAP-${stamp}-2`;
        const pengajuanId = seedDiprosesDenganSurat({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
        });
        const sabtu = nextWeekendYmd();

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);

        await page.locator('[data-test="verifikasi-detail-tanggal-pengambilan"]').fill(sabtu);
        await expect(page.locator('[data-test="verifikasi-detail-jam-kerja-preview"]')).toContainText(/tutup|libur|Sabtu/i);
        await expect(page.locator('[data-test="verifikasi-detail-siap-diambil-button"]')).toBeDisabled();

        expect(getPengajuanStatus(pengajuanId)).toBe('diproses');
    });

    test('riwayat warga menampilkan status siap diambil + tanggal & jam kerja', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.siap.riw.${stamp}@example.com`;
        const wargaEmail = `warga.siap.riw.${stamp}@example.com`;
        const jenisSurat = `Surat Keterangan Tidak Mampu Siap ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin Siap Riw',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga Siap Riw',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureJenisSurat(jenisSurat);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisSurat);
        const nomor = `PJ-E2E-SIAP-${stamp}-3`;
        const pengajuanId = seedDiprosesDenganSurat({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
        });
        const tanggal = nextHariKerjaYmd();

        const marked = runTinker([
            `$p = \\App\\Models\\PengajuanSurat::find(${pengajuanId});`,
            `$hasil = \\App\\Models\\SuratTerbit::tandaiSiapDiambil($p, \\Illuminate\\Support\\Carbon::parse(${JSON.stringify(tanggal)}, 'Asia/Jakarta'));`,
            `echo $hasil['ok'] ? '1' : ('0:' . $hasil['message']);`,
        ].join('')).trim();

        expect(marked.startsWith('1')).toBeTruthy();

        await loginAs(page, wargaEmail);
        await page.goto('/riwayat-pengajuan');

        await expect(page.locator(`[data-test="riwayat-pengajuan-status-${pengajuanId}"]`)).toContainText(/Siap Diambil/i);
        await expect(page.locator(`[data-test="riwayat-pengajuan-tanggal-ambil-${pengajuanId}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="riwayat-pengajuan-jam-kerja-${pengajuanId}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="riwayat-pengajuan-jam-kerja-${pengajuanId}"]`)).toContainText(/WIB/);
    });
});
