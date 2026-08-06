import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-7.4 — QR Code Sekali Pakai (Invalid Setelah Scan Pertama)
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
        `'alamat' => 'Jl. E2E Scan QR No. 1',`,
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
        `'deskripsi' => 'Deskripsi e2e scan qr',`,
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
 * Seed pengajuan siap_diambil + surat_terbit QR valid (US-7.5 UI belum ada).
 */
function seedSiapDiambilDenganQr(options: {
    wargaId: number;
    adminId: number;
    jenisSuratId: number;
    nomorPengajuan: string;
}): { pengajuanId: number; qrToken: string } {
    const php = [
        `$pengajuan = \\App\\Models\\PengajuanSurat::create([`,
        `'user_id' => ${options.wargaId},`,
        `'jenis_surat_id' => ${options.jenisSuratId},`,
        `'nomor_pengajuan' => ${JSON.stringify(options.nomorPengajuan)},`,
        `'keperluan' => 'E2E pengambilan surat QR',`,
        `'status' => \\App\\Models\\PengajuanSurat::STATUS_SIAP_DIAMBIL,`,
        `'diverifikasi_oleh' => ${options.adminId},`,
        `'tanggal_pengajuan' => '2100-07-01',`,
        `]);`,
        `$token = \\Illuminate\\Support\\Str::random(64);`,
        `$surat = \\App\\Models\\SuratTerbit::create([`,
        `'pengajuan_id' => $pengajuan->id,`,
        `'nomor_surat' => '470/' . $pengajuan->id . '/DS-WDN/VIII/2026',`,
        `'file_path' => 'surat-terbit/' . $pengajuan->id . '/surat.pdf',`,
        `'tanggal_terbit' => now()->toDateString(),`,
        `'qr_token' => $token,`,
        `'qr_status' => \\App\\Models\\SuratTerbit::QR_STATUS_VALID,`,
        `'diterbitkan_oleh' => ${options.adminId},`,
        `]);`,
        `\\Illuminate\\Support\\Facades\\Storage::disk('local')->put($surat->file_path, '%PDF-1.4 e2e');`,
        `echo json_encode(['pengajuan_id' => $pengajuan->id, 'qr_token' => $token]);`,
    ].join('');

    const raw = runTinker(php).trim();
    const parsed = JSON.parse(raw) as { pengajuan_id: number; qr_token: string };

    return { pengajuanId: parsed.pengajuan_id, qrToken: parsed.qr_token };
}

function getScanState(pengajuanId: number): {
    status: string;
    qr_status: string | null;
    qr_digunakan_oleh: number | null;
    notif_selesai: boolean;
} {
    const php = [
        `$p = \\App\\Models\\PengajuanSurat::find(${pengajuanId});`,
        `$s = \\App\\Models\\SuratTerbit::where('pengajuan_id', ${pengajuanId})->first();`,
        `$n = \\App\\Models\\Notifikasi::where('pengajuan_id', ${pengajuanId})->where('pesan', 'like', '%selesai%')->exists();`,
        `echo json_encode([`,
        `'status' => $p?->status,`,
        `'qr_status' => $s?->qr_status,`,
        `'qr_digunakan_oleh' => $s?->qr_digunakan_oleh,`,
        `'notif_selesai' => $n,`,
        `]);`,
    ].join('');

    return JSON.parse(runTinker(php).trim());
}

async function loginAs(
    page: import('@playwright/test').Page,
    email: string,
    password = 'password',
): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

test.describe('US-7.4 QR Code Sekali Pakai', () => {
    test('admin dapat membuka halaman Scan QR dari sidebar', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.scan.nav.${stamp}@example.com`;
        ensureUser({
            email,
            name: 'Admin Scan Nav',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });

        await loginAs(page, email);
        await page.locator('[data-test="sidebar-scan-qr-pengambilan"]').click();
        await expect(page).toHaveURL(/\/admin\/scan-qr-pengambilan/);
        await expect(page.locator('[data-test="scan-qr-heading"]')).toBeVisible();
        await expect(page.locator('[data-test="scan-qr-token-input"]')).toBeVisible();
        await expect(page.locator('[data-test="scan-qr-start-camera"]')).toBeVisible();
    });

    test('scan pertama sukses: qr invalid, status selesai, notifikasi warga', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.scan.ok.${stamp}@example.com`;
        const wargaEmail = `warga.scan.ok.${stamp}@example.com`;
        const jenisNama = `Surat Keterangan Domisili Scan ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Scan OK',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Scan OK',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureJenisSurat(jenisNama);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisNama);
        const seeded = seedSiapDiambilDenganQr({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: `PJ-E2E-QR-${stamp}`,
        });

        await loginAs(page, adminEmail);
        await page.goto('/admin/scan-qr-pengambilan');
        await page.locator('[data-test="scan-qr-token-input"]').fill(seeded.qrToken);
        await page.locator('[data-test="scan-qr-submit"]').click();

        await expect(page.locator('[data-test="scan-qr-result"]')).toBeVisible();
        await expect(page.locator('[data-test="scan-qr-result"]')).toHaveAttribute('data-success', '1');
        await expect(page.locator('[data-test="scan-qr-result"]')).toContainText(/berhasil/i);

        const state = getScanState(seeded.pengajuanId);
        expect(state.status).toBe('selesai');
        expect(state.qr_status).toBe('invalid');
        expect(state.qr_digunakan_oleh).toBe(adminId);
        expect(state.notif_selesai).toBe(true);
    });

    test('scan ulang token invalid selalu ditolak (admin berbeda)', async ({ page }) => {
        const stamp = Date.now();
        const admin1Email = `admin.scan.a.${stamp}@example.com`;
        const admin2Email = `admin.scan.b.${stamp}@example.com`;
        const wargaEmail = `warga.scan.reuse.${stamp}@example.com`;
        const jenisNama = `Surat Keterangan Usaha Scan ${stamp}`;

        ensureUser({
            email: admin1Email,
            name: 'Admin Scan A',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: admin2Email,
            name: 'Admin Scan B',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 2),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Scan Reuse',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 3),
        });
        ensureJenisSurat(jenisNama);

        const admin1Id = getUserIdByEmail(admin1Email);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisNama);
        const seeded = seedSiapDiambilDenganQr({
            wargaId,
            adminId: admin1Id,
            jenisSuratId: jenisId,
            nomorPengajuan: `PJ-E2E-QR-REUSE-${stamp}`,
        });

        // Scan pertama oleh admin A
        await loginAs(page, admin1Email);
        await page.goto('/admin/scan-qr-pengambilan');
        await page.locator('[data-test="scan-qr-token-input"]').fill(seeded.qrToken);
        await page.locator('[data-test="scan-qr-submit"]').click();
        await expect(page.locator('[data-test="scan-qr-result"]')).toHaveAttribute('data-success', '1');

        // Ganti sesi ke admin B — scan ulang harus ditolak
        await page.context().clearCookies();
        await loginAs(page, admin2Email);
        await page.goto('/admin/scan-qr-pengambilan');
        await page.locator('[data-test="scan-qr-token-input"]').fill(seeded.qrToken);
        await page.locator('[data-test="scan-qr-submit"]').click();

        await expect(page.locator('[data-test="scan-qr-result"]')).toHaveAttribute('data-success', '0');
        await expect(page.locator('[data-test="scan-qr-result"]')).toContainText(
            'QR sudah digunakan / tidak valid',
        );

        const state = getScanState(seeded.pengajuanId);
        expect(state.status).toBe('selesai');
        expect(state.qr_status).toBe('invalid');
        expect(state.qr_digunakan_oleh).toBe(admin1Id);
    });

    test('token tidak dikenal dan status belum siap_diambil ditolak', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.scan.reject.${stamp}@example.com`;
        const wargaEmail = `warga.scan.reject.${stamp}@example.com`;
        const jenisNama = `Surat Keterangan Domisili Reject ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Scan Reject',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Scan Reject',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 4),
        });
        ensureJenisSurat(jenisNama);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisNama);

        // Seed diproses (bukan siap_diambil) + QR valid
        const php = [
            `$pengajuan = \\App\\Models\\PengajuanSurat::create([`,
            `'user_id' => ${wargaId},`,
            `'jenis_surat_id' => ${jenisId},`,
            `'nomor_pengajuan' => ${JSON.stringify(`PJ-E2E-QR-EARLY-${stamp}`)},`,
            `'keperluan' => 'E2E terlalu dini',`,
            `'status' => \\App\\Models\\PengajuanSurat::STATUS_DIPROSES,`,
            `'diverifikasi_oleh' => ${adminId},`,
            `'tanggal_pengajuan' => '2100-07-02',`,
            `]);`,
            `$token = \\Illuminate\\Support\\Str::random(64);`,
            `\\App\\Models\\SuratTerbit::create([`,
            `'pengajuan_id' => $pengajuan->id,`,
            `'nomor_surat' => '470/' . $pengajuan->id . '/DS-WDN/VIII/2026',`,
            `'file_path' => 'surat-terbit/' . $pengajuan->id . '/surat.pdf',`,
            `'tanggal_terbit' => now()->toDateString(),`,
            `'qr_token' => $token,`,
            `'qr_status' => 'valid',`,
            `'diterbitkan_oleh' => ${adminId},`,
            `]);`,
            `echo json_encode(['pengajuan_id' => $pengajuan->id, 'qr_token' => $token]);`,
        ].join('');
        const early = JSON.parse(runTinker(php).trim()) as { pengajuan_id: number; qr_token: string };

        await loginAs(page, adminEmail);
        await page.goto('/admin/scan-qr-pengambilan');

        // Unknown token
        await page.locator('[data-test="scan-qr-token-input"]').fill('x'.repeat(64));
        await page.locator('[data-test="scan-qr-submit"]').click();
        await expect(page.locator('[data-test="scan-qr-result"]')).toHaveAttribute('data-success', '0');
        await expect(page.locator('[data-test="scan-qr-result"]')).toContainText('tidak dikenal');

        // Token valid tapi status belum siap_diambil
        await page.locator('[data-test="scan-qr-token-input"]').fill(early.qr_token);
        await page.locator('[data-test="scan-qr-submit"]').click();
        await expect(page.locator('[data-test="scan-qr-result"]')).toHaveAttribute('data-success', '0');
        await expect(page.locator('[data-test="scan-qr-result"]')).toContainText('belum siap diambil');

        const state = getScanState(early.pengajuan_id);
        expect(state.status).toBe('diproses');
        expect(state.qr_status).toBe('valid');
    });

    test('warga tidak dapat mengakses halaman scan qr', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.scan.forbid.${stamp}@example.com`;
        ensureUser({
            email,
            name: 'Warga Scan Forbid',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 5),
        });

        await loginAs(page, email);
        const response = await page.goto('/admin/scan-qr-pengambilan');
        expect(response?.status()).toBe(403);
    });
});
