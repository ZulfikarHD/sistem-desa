import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-7.6 — Unduh/Cetak Surat oleh Warga
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

function runTinker(php: string): string {
    return execFileSync('php', ['artisan', 'tinker', '--execute', php], {
        cwd: projectRoot,
        encoding: 'utf8',
    });
}

function uniqueNik(suffix: number): string {
    return `3208080808${String(suffix).padStart(6, '0')}`;
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
        `'alamat' => 'Jl. E2E Unduh Surat No. 1',`,
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
        `'deskripsi' => 'Deskripsi e2e unduh surat',`,
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
 * Seed pengajuan + surat_terbit PDF untuk unduh warga.
 */
function seedPengajuanDenganSurat(options: {
    wargaId: number;
    adminId: number;
    jenisSuratId: number;
    nomorPengajuan: string;
    status: 'diproses' | 'siap_diambil' | 'selesai' | 'diajukan';
    tanggalPengambilan?: string | null;
    jamKerjaLabel?: string | null;
}): number {
    const tanggal = options.tanggalPengambilan ?? null;
    const jam = options.jamKerjaLabel ?? null;
    const php = [
        `$pengajuan = \\App\\Models\\PengajuanSurat::create([`,
        `'user_id' => ${options.wargaId},`,
        `'jenis_surat_id' => ${options.jenisSuratId},`,
        `'nomor_pengajuan' => ${JSON.stringify(options.nomorPengajuan)},`,
        `'keperluan' => 'E2E unduh surat warga',`,
        `'status' => ${JSON.stringify(options.status)},`,
        `'diverifikasi_oleh' => ${options.status === 'diajukan' ? 'null' : options.adminId},`,
        `'tanggal_pengajuan' => '2100-08-01',`,
        `]);`,
        `if (${JSON.stringify(options.status)} !== 'diajukan') {`,
        `$token = \\Illuminate\\Support\\Str::random(64);`,
        `$path = 'surat-terbit/' . $pengajuan->id . '/surat.pdf';`,
        `\\Illuminate\\Support\\Facades\\Storage::disk('local')->put($path, '%PDF-1.4 e2e unduh surat');`,
        `\\App\\Models\\SuratTerbit::create([`,
        `'pengajuan_id' => $pengajuan->id,`,
        `'nomor_surat' => '470/' . $pengajuan->id . '/DS-WDN/VIII/2026',`,
        `'file_path' => $path,`,
        `'tanggal_terbit' => now()->toDateString(),`,
        `'tanggal_pengambilan' => ${tanggal ? JSON.stringify(tanggal) : 'null'},`,
        `'jam_kerja_label' => ${jam ? JSON.stringify(jam) : 'null'},`,
        `'qr_token' => $token,`,
        `'qr_status' => \\App\\Models\\SuratTerbit::QR_STATUS_VALID,`,
        `'diterbitkan_oleh' => ${options.adminId},`,
        `]);`,
        `}`,
        `echo $pengajuan->id;`,
    ].join('');

    const id = Number(runTinker(php).trim());
    if (!id) {
        throw new Error(`Failed to seed pengajuan: ${options.nomorPengajuan}`);
    }

    return id;
}

function getQrSnapshot(pengajuanId: number): { qr_token: string; qr_status: string; file_path: string } {
    const raw = runTinker([
        `$s = \\App\\Models\\SuratTerbit::where('pengajuan_id', ${pengajuanId})->first();`,
        `echo json_encode([`,
        `'qr_token' => $s?->qr_token,`,
        `'qr_status' => $s?->qr_status,`,
        `'file_path' => $s?->file_path,`,
        `]);`,
    ].join('')).trim();

    return JSON.parse(raw) as { qr_token: string; qr_status: string; file_path: string };
}

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

test.describe('US-7.6 Unduh/Cetak Surat Warga', () => {
    test('warga unduh surat dari riwayat untuk status diproses tanpa regenerasi QR', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.unduh.ok.${stamp}@example.com`;
        const wargaEmail = `warga.unduh.ok.${stamp}@example.com`;
        const jenisSurat = `Surat Keterangan Domisili Unduh ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin Unduh OK',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga Unduh OK',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureJenisSurat(jenisSurat);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisSurat);
        const nomor = `PJ-E2E-UNDUH-${stamp}-1`;
        const pengajuanId = seedPengajuanDenganSurat({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
            status: 'diproses',
        });

        const sebelum = getQrSnapshot(pengajuanId);

        await loginAs(page, wargaEmail);
        await page.goto('/riwayat-pengajuan');

        const unduh = page.locator(`[data-test="riwayat-pengajuan-unduh-surat-${pengajuanId}"]`);
        await expect(unduh).toBeVisible();

        const downloadPromise = page.waitForEvent('download');
        await unduh.click();
        const download = await downloadPromise;

        expect(download.suggestedFilename()).toMatch(/\.pdf$/i);
        const downloadPath = await download.path();
        expect(downloadPath).toBeTruthy();
        const content = fs.readFileSync(downloadPath!);
        expect(content.toString('utf8')).toContain('%PDF');

        const sesudah = getQrSnapshot(pengajuanId);
        expect(sesudah.qr_token).toBe(sebelum.qr_token);
        expect(sesudah.qr_status).toBe(sebelum.qr_status);
        expect(sesudah.file_path).toBe(sebelum.file_path);
    });

    test('detail menampilkan tanggal pengambilan jam kerja dan unduh ulang tetap QR sama', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.unduh.det.${stamp}@example.com`;
        const wargaEmail = `warga.unduh.det.${stamp}@example.com`;
        const jenisSurat = `Surat Keterangan Usaha Unduh ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin Unduh Det',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga Unduh Det',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureJenisSurat(jenisSurat);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisSurat);
        const nomor = `PJ-E2E-UNDUH-${stamp}-2`;
        const pengajuanId = seedPengajuanDenganSurat({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
            status: 'siap_diambil',
            tanggalPengambilan: '2100-08-12',
            jamKerjaLabel: 'Senin–Kamis 08.00–16.00 WIB',
        });

        const sebelum = getQrSnapshot(pengajuanId);

        await loginAs(page, wargaEmail);
        await page.goto(`/pengajuan-surat/detail/${pengajuanId}`);

        await expect(page.locator('[data-test="detail-pengajuan-warga-tanggal-pengambilan"]')).toBeVisible();
        await expect(page.locator('[data-test="detail-pengajuan-warga-jam-kerja"]')).toContainText(/WIB/);
        await expect(page.locator('[data-test="detail-pengajuan-warga-unduh-surat"]')).toBeVisible();
        await expect(page.locator('[data-test="detail-pengajuan-warga-cetak-surat"]')).toBeVisible();

        const downloadPromise = page.waitForEvent('download');
        await page.locator('[data-test="detail-pengajuan-warga-unduh-surat"]').click();
        const download = await downloadPromise;
        expect(download.suggestedFilename()).toMatch(/\.pdf$/i);

        const sesudah = getQrSnapshot(pengajuanId);
        expect(sesudah.qr_token).toBe(sebelum.qr_token);
        expect(sesudah.qr_status).toBe('valid');
    });

    test('edge: unduh tetap berhasil jika file PDF hilang tanpa regenerasi QR', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.unduh.miss.${stamp}@example.com`;
        const wargaEmail = `warga.unduh.miss.${stamp}@example.com`;
        const jenisSurat = `Surat Keterangan Domisili Missing ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin Unduh Missing',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga Unduh Missing',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureJenisSurat(jenisSurat);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisSurat);
        const nomor = `PJ-E2E-UNDUH-${stamp}-miss`;
        const pengajuanId = seedPengajuanDenganSurat({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
            status: 'selesai',
        });

        const sebelum = getQrSnapshot(pengajuanId);

        // Hapus file PDF di disk — hybrid harus regenerate tanpa mint QR baru.
        runTinker([
            `$s = \\App\\Models\\SuratTerbit::where('pengajuan_id', ${pengajuanId})->first();`,
            `\\Illuminate\\Support\\Facades\\Storage::disk('local')->delete($s->file_path);`,
            `echo \\Illuminate\\Support\\Facades\\Storage::disk('local')->exists($s->file_path) ? '1' : '0';`,
        ].join(''));

        await loginAs(page, wargaEmail);
        await page.goto(`/pengajuan-surat/detail/${pengajuanId}`);

        const unduh = page.locator('[data-test="detail-pengajuan-warga-unduh-surat"]');
        await expect(unduh).toBeVisible();

        const downloadPromise = page.waitForEvent('download');
        await unduh.click();
        const download = await downloadPromise;

        expect(download.suggestedFilename()).toMatch(/\.pdf$/i);
        const downloadPath = await download.path();
        expect(downloadPath).toBeTruthy();
        const content = fs.readFileSync(downloadPath!);
        expect(content.toString('utf8', 0, 5)).toContain('%PDF');

        const sesudah = getQrSnapshot(pengajuanId);
        expect(sesudah.qr_token).toBe(sebelum.qr_token);
        expect(sesudah.qr_status).toBe(sebelum.qr_status);
        expect(sesudah.file_path).toBe(`surat-terbit/${pengajuanId}/surat.pdf`);
    });

    test('edge: status diajukan tidak menampilkan unduh; warga lain mendapat 403', async ({ page, browser }) => {
        const stamp = Date.now();
        const adminEmail = `admin.unduh.edge.${stamp}@example.com`;
        const wargaEmail = `warga.unduh.edge.${stamp}@example.com`;
        const otherEmail = `warga.unduh.other.${stamp}@example.com`;
        const jenisSurat = `Surat Keterangan Tidak Mampu Unduh ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin Unduh Edge',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga Unduh Edge',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureUser({
            email: otherEmail,
            name: 'E2E Warga Unduh Other',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 2),
        });
        ensureJenisSurat(jenisSurat);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisSurat);

        const diajukanId = seedPengajuanDenganSurat({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: `PJ-E2E-UNDUH-${stamp}-3a`,
            status: 'diajukan',
        });

        const diprosesId = seedPengajuanDenganSurat({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: `PJ-E2E-UNDUH-${stamp}-3b`,
            status: 'diproses',
        });

        await loginAs(page, wargaEmail);
        await page.goto('/riwayat-pengajuan');
        await expect(page.locator(`[data-test="riwayat-pengajuan-unduh-surat-${diajukanId}"]`)).toHaveCount(0);
        await expect(page.locator(`[data-test="riwayat-pengajuan-unduh-surat-${diprosesId}"]`)).toBeVisible();

        const otherContext = await browser.newContext();
        const otherPage = await otherContext.newPage();
        await loginAs(otherPage, otherEmail);
        const response = await otherPage.goto(`/pengajuan-surat/${diprosesId}/unduh-surat`);
        expect(response?.status()).toBe(403);
        await otherContext.close();
    });
});
