import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-6.1 — Halaman Rekap Pengajuan dengan Filter
 * US-6.2 — Export Data Rekap (CSV)
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

function runTinker(php: string): string {
    return execFileSync('php', ['artisan', 'tinker', '--execute', php], {
        cwd: projectRoot,
        encoding: 'utf8',
    });
}

function uniqueNik(suffix: number): string {
    return `3207070707${String(suffix).padStart(6, '0')}`;
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
        `'alamat' => 'Jl. E2E Rekap No. 1',`,
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
        `'deskripsi' => 'Deskripsi e2e rekap',`,
        `'persyaratan_dokumen' => '- Fotokopi KTP\\n- Fotokopi KK',`,
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

function ensurePengajuan(options: {
    userId: number;
    jenisSuratId: number;
    nomorPengajuan: string;
    status: 'diajukan' | 'diproses' | 'disetujui' | 'ditolak';
    tanggalPengajuan: string;
    diverifikasiOleh?: number | null;
}): number {
    const diverifikasi = options.diverifikasiOleh ?? 'null';
    const catatan =
        options.status === 'ditolak' ? JSON.stringify('Dokumen tidak lengkap (e2e)') : 'null';

    const php = [
        `$existing = \\App\\Models\\PengajuanSurat::where('nomor_pengajuan', ${JSON.stringify(options.nomorPengajuan)})->first();`,
        `if ($existing) { echo $existing->id; return; }`,
        `$pengajuan = \\App\\Models\\PengajuanSurat::create([`,
        `'user_id' => ${options.userId},`,
        `'jenis_surat_id' => ${options.jenisSuratId},`,
        `'nomor_pengajuan' => ${JSON.stringify(options.nomorPengajuan)},`,
        `'keperluan' => 'Keperluan e2e rekap pengajuan',`,
        `'status' => ${JSON.stringify(options.status)},`,
        `'catatan_admin' => ${catatan},`,
        `'diverifikasi_oleh' => ${diverifikasi},`,
        `'tanggal_pengajuan' => ${JSON.stringify(options.tanggalPengajuan)},`,
        `]);`,
        `echo $pengajuan->id;`,
    ].join('');

    const id = Number(runTinker(php).trim());
    if (!id) {
        throw new Error(`Failed to create pengajuan ${options.nomorPengajuan}`);
    }

    return id;
}

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

test.describe('US-6.1 + US-6.2 Rekap Pengajuan & Export CSV', () => {
    test('admin can open rekap page with summary table filters and sidebar link', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.rekap.ok.${stamp}@example.com`;
        const wargaEmail = `warga.rekap.ok.${stamp}@example.com`;
        const jenisA = `Surat Domisili Rekap ${stamp}`;
        const jenisB = `Surat SKTM Rekap ${stamp}`;
        const nomorDiajukan = `PJ-E2E-REKAP-${stamp}-1`;
        const nomorDisetujui = `PJ-E2E-REKAP-${stamp}-2`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Rekap E2E',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Rekap E2E',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });

        ensureJenisSurat(jenisA);
        ensureJenisSurat(jenisB);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisAId = getJenisSuratIdByName(jenisA);
        const jenisBId = getJenisSuratIdByName(jenisB);

        const tanggalDiajukan = '2090-06-01';
        const tanggalDisetujui = '2090-06-15';

        const diajukanId = ensurePengajuan({
            userId: wargaId,
            jenisSuratId: jenisAId,
            nomorPengajuan: nomorDiajukan,
            status: 'diajukan',
            tanggalPengajuan: tanggalDiajukan,
        });

        const disetujuiId = ensurePengajuan({
            userId: wargaId,
            jenisSuratId: jenisBId,
            nomorPengajuan: nomorDisetujui,
            status: 'disetujui',
            tanggalPengajuan: tanggalDisetujui,
            diverifikasiOleh: adminId,
        });

        await loginAs(page, adminEmail);
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        await page.locator('[data-test="sidebar-rekap-pengajuan"]').click();
        await expect(page).toHaveURL(/\/admin\/rekap-pengajuan/);
        await expect(page.locator('[data-test="rekap-pengajuan-heading"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-pengajuan-ringkasan"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-ringkasan-total"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-ringkasan-diajukan"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-ringkasan-diproses"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-ringkasan-disetujui"]')).toBeVisible();
        await expect(page.locator('[data-test="rekap-ringkasan-ditolak"]')).toBeVisible();

        // Isolasi baris uji lewat filter jenis agar tidak tertutup pagination data e2e lain
        await page.locator('[data-test="rekap-pengajuan-jenis-filter"]').selectOption(String(jenisAId));
        await expect(page.locator(`[data-test="rekap-pengajuan-row-${diajukanId}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="rekap-pengajuan-nomor-${diajukanId}"]`)).toContainText(nomorDiajukan);
        await expect(page.locator(`[data-test="rekap-pengajuan-warga-${diajukanId}"]`)).toContainText('Warga Rekap E2E');
        await expect(page.locator(`[data-test="rekap-pengajuan-row-${disetujuiId}"]`)).toHaveCount(0);

        await page.locator('[data-test="rekap-pengajuan-jenis-filter"]').selectOption(String(jenisBId));
        await expect(page.locator(`[data-test="rekap-pengajuan-row-${disetujuiId}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="rekap-pengajuan-admin-${disetujuiId}"]`)).toContainText('Admin Rekap E2E');

        // Filter status disetujui — hanya baris disetujui
        await page.locator('[data-test="rekap-pengajuan-status-filter"]').selectOption('disetujui');
        await expect(page.locator(`[data-test="rekap-pengajuan-row-${disetujuiId}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="rekap-pengajuan-row-${diajukanId}"]`)).toHaveCount(0);

        // Filter jenis surat A + reset status ke semua
        await page.locator('[data-test="rekap-pengajuan-status-filter"]').selectOption('');
        await page.locator('[data-test="rekap-pengajuan-jenis-filter"]').selectOption(String(jenisAId));
        await expect(page.locator(`[data-test="rekap-pengajuan-row-${diajukanId}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="rekap-pengajuan-row-${disetujuiId}"]`)).toHaveCount(0);

        // Filter rentang tanggal unik (far-future) agar tidak bentrok pagination data e2e lain
        await page.locator('[data-test="rekap-pengajuan-reset-filters"]').click();
        await expect(page.locator('[data-test="rekap-pengajuan-jenis-filter"]')).toHaveValue('');
        await expect(page.locator('[data-test="rekap-pengajuan-status-filter"]')).toHaveValue('');
        await page.locator('[data-test="rekap-pengajuan-tanggal-dari"]').fill(tanggalDisetujui);
        await page.locator('[data-test="rekap-pengajuan-tanggal-sampai"]').fill(tanggalDisetujui);
        await expect(page.locator(`[data-test="rekap-pengajuan-row-${disetujuiId}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="rekap-pengajuan-row-${diajukanId}"]`)).toHaveCount(0);
    });

    test('export csv downloads filtered rows with matching columns', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.rekap.export.${stamp}@example.com`;
        const wargaEmail = `warga.rekap.export.${stamp}@example.com`;
        const jenisA = `Jenis Export A ${stamp}`;
        const jenisB = `Jenis Export B ${stamp}`;
        const nomorIncluded = `PJ-E2E-EXPORT-${stamp}-1`;
        const nomorExcluded = `PJ-E2E-EXPORT-${stamp}-2`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Export E2E',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 10),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Export E2E',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 11),
        });

        ensureJenisSurat(jenisA);
        ensureJenisSurat(jenisB);

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisAId = getJenisSuratIdByName(jenisA);
        const jenisBId = getJenisSuratIdByName(jenisB);

        ensurePengajuan({
            userId: wargaId,
            jenisSuratId: jenisAId,
            nomorPengajuan: nomorIncluded,
            status: 'disetujui',
            tanggalPengajuan: '2026-08-05',
            diverifikasiOleh: adminId,
        });

        ensurePengajuan({
            userId: wargaId,
            jenisSuratId: jenisBId,
            nomorPengajuan: nomorExcluded,
            status: 'diajukan',
            tanggalPengajuan: '2026-08-05',
        });

        await loginAs(page, adminEmail);
        await page.goto('/admin/rekap-pengajuan');
        await expect(page.locator('[data-test="rekap-pengajuan-heading"]')).toBeVisible();

        await page.locator('[data-test="rekap-pengajuan-jenis-filter"]').selectOption(String(jenisAId));
        await page.locator('[data-test="rekap-pengajuan-status-filter"]').selectOption('disetujui');

        const downloadPromise = page.waitForEvent('download');
        await page.locator('[data-test="rekap-pengajuan-export-csv"]').click();
        const download = await downloadPromise;

        expect(download.suggestedFilename()).toMatch(/^rekap-pengajuan-.*\.csv$/);

        const downloadPath = await download.path();
        expect(downloadPath).toBeTruthy();

        const fs = await import('node:fs');
        const content = fs.readFileSync(downloadPath!, 'utf8');

        // UTF-8 BOM
        const raw = fs.readFileSync(downloadPath!);
        expect(raw[0]).toBe(0xef);
        expect(raw[1]).toBe(0xbb);
        expect(raw[2]).toBe(0xbf);

        expect(content).toContain('Nomor Pengajuan');
        expect(content).toContain('Nama Warga');
        expect(content).toContain('Jenis Surat');
        expect(content).toContain('Tanggal Pengajuan');
        expect(content).toContain('Status');
        expect(content).toContain('Admin Verifikator');
        expect(content).toContain(nomorIncluded);
        expect(content).toContain('Warga Export E2E');
        expect(content).toContain(jenisA);
        expect(content).toContain('Admin Export E2E');
        expect(content).not.toContain(nomorExcluded);
    });

    test('invalid date range shows validation error (edge case)', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.rekap.date.${stamp}@example.com`;
        const wargaEmail = `warga.rekap.date.${stamp}@example.com`;
        const jenisNama = `Jenis Date Edge ${stamp}`;
        const nomor = `PJ-E2E-DATE-${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Date Edge',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 20),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Date Edge',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 21),
        });
        ensureJenisSurat(jenisNama);

        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = getJenisSuratIdByName(jenisNama);
        const pengajuanId = ensurePengajuan({
            userId: wargaId,
            jenisSuratId: jenisId,
            nomorPengajuan: nomor,
            status: 'diajukan',
            tanggalPengajuan: '2026-08-10',
        });

        await loginAs(page, adminEmail);
        await page.goto('/admin/rekap-pengajuan');
        // Isolasi baris uji lewat filter jenis (shared DB + pagination)
        await page.locator('[data-test="rekap-pengajuan-jenis-filter"]').selectOption(String(jenisId));
        await expect(page.locator(`[data-test="rekap-pengajuan-row-${pengajuanId}"]`)).toBeVisible();

        await page.locator('[data-test="rekap-pengajuan-tanggal-dari"]').fill('2026-08-31');
        await page.locator('[data-test="rekap-pengajuan-tanggal-sampai"]').fill('2026-08-01');

        await expect(page.getByText('Tanggal sampai harus sama atau setelah tanggal dari.')).toBeVisible();
        await expect(page.locator(`[data-test="rekap-pengajuan-row-${pengajuanId}"]`)).toHaveCount(0);
        await expect(page.locator('[data-test="rekap-pengajuan-empty"]')).toBeVisible();
    });

    test('warga cannot access rekap pengajuan page', async ({ page }) => {
        const stamp = Date.now();
        const wargaEmail = `warga.rekap.forbid.${stamp}@example.com`;

        ensureUser({
            email: wargaEmail,
            name: 'Warga Rekap Forbid',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 30),
        });

        await loginAs(page, wargaEmail);
        await expect(page).toHaveURL(/\/dashboard$/);

        const response = await page.goto('/admin/rekap-pengajuan');
        expect(response?.status()).toBe(403);
        await expect(page.locator('[data-test="rekap-pengajuan-heading"]')).toHaveCount(0);
    });

    test('empty filters show empty state message', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.rekap.empty.${stamp}@example.com`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Rekap Empty',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 40),
        });

        await loginAs(page, adminEmail);
        await page.goto('/admin/rekap-pengajuan');

        await page.locator('[data-test="rekap-pengajuan-tanggal-dari"]').fill('2099-01-01');
        await page.locator('[data-test="rekap-pengajuan-tanggal-sampai"]').fill('2099-12-31');

        await expect(page.locator('[data-test="rekap-pengajuan-empty"]')).toBeVisible();
        await expect(page.getByText('Tidak ada pengajuan yang cocok dengan filter saat ini.')).toBeVisible();
    });
});
