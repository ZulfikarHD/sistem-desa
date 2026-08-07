import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-8.7 — Timeline Proses di Detail Rekap Pengajuan (Admin)
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
        `'alamat' => 'Jl. E2E Rekap Timeline No. 1',`,
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
        `'deskripsi' => 'Deskripsi e2e rekap timeline',`,
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

function seedPengajuanSelesaiDenganTimeline(options: {
    wargaId: number;
    adminId: number;
    adminScanId: number;
    jenisSuratId: number;
    nomorPengajuan: string;
}): number {
    const php = [
        `$pengajuan = \\App\\Models\\PengajuanSurat::create([`,
        `'user_id' => ${options.wargaId},`,
        `'jenis_surat_id' => ${options.jenisSuratId},`,
        `'nomor_pengajuan' => ${JSON.stringify(options.nomorPengajuan)},`,
        `'keperluan' => 'E2E rekap timeline US-8.7',`,
        `'status' => \\App\\Models\\PengajuanSurat::STATUS_SELESAI,`,
        `'diverifikasi_oleh' => ${options.adminId},`,
        `'tanggal_pengajuan' => '2100-09-01',`,
        `]);`,
        `\\App\\Models\\LogVerifikasi::query()->create([`,
        `'pengajuan_id' => $pengajuan->id,`,
        `'admin_id' => ${options.adminId},`,
        `'aksi' => \\App\\Models\\LogVerifikasi::AKSI_SETUJUI,`,
        `'keterangan' => null,`,
        `'created_at' => now()->subDays(3),`,
        `]);`,
        `$token = \\Illuminate\\Support\\Str::random(64);`,
        `$path = 'surat-terbit/' . $pengajuan->id . '/surat.pdf';`,
        `\\Illuminate\\Support\\Facades\\Storage::disk('local')->put($path, '%PDF-1.4 e2e rekap timeline');`,
        `\\App\\Models\\SuratTerbit::create([`,
        `'pengajuan_id' => $pengajuan->id,`,
        `'nomor_surat' => '470/' . $pengajuan->id . '/DS-WDN/IX/2026',`,
        `'file_path' => $path,`,
        `'tanggal_terbit' => now()->subDays(3)->toDateString(),`,
        `'tanggal_pengambilan' => now('Asia/Jakarta')->toDateString(),`,
        `'siap_diambil_at' => now()->subDay(),`,
        `'jam_kerja_label' => 'Senin–Kamis 08.00–16.00 WIB',`,
        `'qr_token' => $token,`,
        `'qr_status' => \\App\\Models\\SuratTerbit::QR_STATUS_INVALID,`,
        `'qr_digunakan_at' => now()->subHour(),`,
        `'qr_digunakan_oleh' => ${options.adminScanId},`,
        `'diterbitkan_oleh' => ${options.adminId},`,
        `]);`,
        `echo $pengajuan->id;`,
    ].join('');

    const id = Number(runTinker(php).trim());
    if (!id) {
        throw new Error(`Failed to seed selesai pengajuan: ${options.nomorPengajuan}`);
    }

    return id;
}

function seedPengajuanDitolak(options: {
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
        `'keperluan' => 'E2E rekap timeline ditolak',`,
        `'status' => \\App\\Models\\PengajuanSurat::STATUS_DITOLAK,`,
        `'catatan_admin' => 'Dokumen tidak lengkap (e2e timeline)',`,
        `'diverifikasi_oleh' => ${options.adminId},`,
        `'tanggal_pengajuan' => '2100-09-02',`,
        `]);`,
        `\\App\\Models\\LogVerifikasi::query()->create([`,
        `'pengajuan_id' => $pengajuan->id,`,
        `'admin_id' => ${options.adminId},`,
        `'aksi' => \\App\\Models\\LogVerifikasi::AKSI_TOLAK,`,
        `'keterangan' => 'Dokumen tidak lengkap (e2e timeline)',`,
        `'created_at' => now()->subDay(),`,
        `]);`,
        `echo $pengajuan->id;`,
    ].join('');

    const id = Number(runTinker(php).trim());
    if (!id) {
        throw new Error(`Failed to seed ditolak pengajuan: ${options.nomorPengajuan}`);
    }

    return id;
}

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

test.describe('US-8.7 Timeline Detail Rekap Pengajuan', () => {
    test('admin can open detail from rekap and see full timeline with PDF download', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.rekap.tl.${stamp}@example.com`;
        const adminScanEmail = `admin.scan.tl.${stamp}@example.com`;
        const wargaEmail = `warga.rekap.tl.${stamp}@example.com`;
        const jenisNama = `Surat Domisili Timeline ${stamp}`;
        const nomor = `PJ-E2E-TL-${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Rekap Timeline',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: adminScanEmail,
            name: 'Admin Scan Timeline',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Rekap Timeline',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 2),
        });
        ensureJenisSurat(jenisNama);

        const adminId = getUserIdByEmail(adminEmail);
        const adminScanId = getUserIdByEmail(adminScanEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisNama);

        const pengajuanId = seedPengajuanSelesaiDenganTimeline({
            wargaId,
            adminId,
            adminScanId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
        });

        await loginAs(page, adminEmail);
        await page.goto('/admin/rekap-pengajuan');
        await expect(page.locator('[data-test="rekap-pengajuan-heading"]')).toBeVisible();

        await page.locator('[data-test="rekap-pengajuan-jenis-filter"]').selectOption(String(jenisId));
        await expect(page.locator(`[data-test="rekap-pengajuan-row-${pengajuanId}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="rekap-pengajuan-detail-${pengajuanId}"]`)).toBeVisible();

        await page.locator(`[data-test="rekap-pengajuan-detail-${pengajuanId}"]`).click();
        await expect(page).toHaveURL(new RegExp(`/admin/rekap-pengajuan/${pengajuanId}`));

        await expect(page.locator('[data-test="rekap-detail-heading"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-detail-ringkasan"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-detail-nama"]')).toContainText('Warga Rekap Timeline');
        await expect(page.locator('[data-test="rekap-detail-timeline"]')).toBeVisible();

        await expect(page.locator('[data-test="rekap-timeline-item-dibuat"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-timeline-item-disetujui_diproses"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-timeline-item-siap_diambil"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-timeline-item-selesai"]')).toBeVisible();

        await expect(page.locator('[data-test="rekap-timeline-label-dibuat"]')).toContainText(
            'Pengajuan diterima oleh sistem',
        );
        await expect(page.locator('[data-test="rekap-timeline-label-disetujui_diproses"]')).toContainText(
            'Disetujui oleh Admin Rekap Timeline',
        );
        await expect(page.locator('[data-test="rekap-timeline-label-siap_diambil"]')).toContainText(
            'Dokumen siap diambil oleh Admin Rekap Timeline',
        );
        await expect(page.locator('[data-test="rekap-timeline-label-selesai"]')).toContainText(
            'Admin Scan Timeline',
        );
        await expect(page.locator('[data-test="rekap-timeline-waktu-dibuat"]')).toContainText('WIB');

        await expect(page.locator('[data-test="rekap-detail-unduh-pdf"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-detail-kembali"]')).toBeVisible();

        await page.locator('[data-test="rekap-detail-kembali"]').click();
        await expect(page).toHaveURL(/\/admin\/rekap-pengajuan/);
        await expect(page.locator('[data-test="rekap-pengajuan-heading"]')).toBeVisible();
    });

    test('ditolak timeline stops at rejection without siap diambil or selesai (edge case)', async ({
        page,
    }) => {
        const stamp = Date.now();
        const adminEmail = `admin.rekap.tl.reject.${stamp}@example.com`;
        const wargaEmail = `warga.rekap.tl.reject.${stamp}@example.com`;
        const jenisNama = `Surat SKTM Timeline Reject ${stamp}`;
        const nomor = `PJ-E2E-TL-REJ-${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Timeline Reject',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 10),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Timeline Reject',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 11),
        });
        ensureJenisSurat(jenisNama);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisNama);

        const pengajuanId = seedPengajuanDitolak({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/rekap-pengajuan/${pengajuanId}`);

        await expect(page.locator('[data-test="rekap-detail-heading"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-timeline-item-dibuat"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-timeline-item-ditolak"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-timeline-label-ditolak"]')).toContainText(
            'Ditolak oleh Admin Timeline Reject',
        );
        await expect(page.locator('[data-test="rekap-timeline-label-ditolak"]')).toContainText(
            'Dokumen tidak lengkap (e2e timeline)',
        );

        await expect(page.locator('[data-test="rekap-timeline-item-disetujui_diproses"]')).toHaveCount(0);
        await expect(page.locator('[data-test="rekap-timeline-item-siap_diambil"]')).toHaveCount(0);
        await expect(page.locator('[data-test="rekap-timeline-item-selesai"]')).toHaveCount(0);
        await expect(page.locator('[data-test="rekap-detail-unduh-pdf"]')).toHaveCount(0);
    });

    test('warga cannot access rekap detail page', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.rekap.tl.forbid.${stamp}@example.com`;
        const wargaEmail = `warga.rekap.tl.forbid.${stamp}@example.com`;
        const jenisNama = `Jenis Forbid Timeline ${stamp}`;
        const nomor = `PJ-E2E-TL-FORBID-${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Forbid Timeline',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 20),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Forbid Timeline',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 21),
        });
        ensureJenisSurat(jenisNama);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisNama);

        const pengajuanId = seedPengajuanDitolak({
            wargaId,
            adminId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
        });

        await loginAs(page, wargaEmail);
        await expect(page).toHaveURL(/\/dashboard$/);

        const response = await page.goto(`/admin/rekap-pengajuan/${pengajuanId}`);
        expect(response?.status()).toBe(403);
        await expect(page.locator('[data-test="rekap-detail-heading"]')).toHaveCount(0);
    });
});
