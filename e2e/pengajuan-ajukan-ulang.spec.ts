import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-3.4 — Ajukan Ulang Setelah Ditolak
 * Happy path: riwayat → Ajukan Ulang → form terisi → upload dokumen → nomor baru.
 * Edge/failure: tombol tidak muncul untuk status non-ditolak, submit tanpa dokumen gagal.
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const fixturesDir = path.join(projectRoot, 'e2e', 'fixtures');

function uniqueNik(suffix: number): string {
    return `3205050505${String(suffix).padStart(6, '0')}`;
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
        `'alamat' => 'Jl. E2E Ajukan Ulang No. 1',`,
        `'role' => ${JSON.stringify(options.role)},`,
        `'password' => ${JSON.stringify(password)},`,
        `'email_verified_at' => now(),`,
        `]`,
        `);`,
    ].join('');

    execSync(`php artisan tinker --execute ${JSON.stringify(php)}`, {
        cwd: projectRoot,
        stdio: 'pipe',
    });
}

function ensureJenisSurat(
    namaSurat: string,
    persyaratanDokumen = '- Fotokopi KTP\n- Fotokopi KK',
): void {
    const php = [
        `\\App\\Models\\JenisSurat::updateOrCreate(`,
        `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
        `[`,
        `'deskripsi' => 'Deskripsi e2e ajukan ulang',`,
        `'persyaratan_dokumen' => ${JSON.stringify(persyaratanDokumen)},`,
        `]`,
        `);`,
    ].join('');

    execSync(`php artisan tinker --execute ${JSON.stringify(php)}`, {
        cwd: projectRoot,
        stdio: 'pipe',
    });
}

function seedDitolakPengajuan(options: {
    email: string;
    namaSurat: string;
    keperluan: string;
    catatanAdmin: string;
    nomorPengajuan: string;
}): void {
    const php = [
        `\\App\\Models\\PengajuanSurat::updateOrCreate(`,
        `['nomor_pengajuan' => ${JSON.stringify(options.nomorPengajuan)}],`,
        `[`,
        `'user_id' => \\App\\Models\\User::where('email', ${JSON.stringify(options.email)})->value('id'),`,
        `'jenis_surat_id' => \\App\\Models\\JenisSurat::where('nama_surat', ${JSON.stringify(options.namaSurat)})->value('id'),`,
        `'keperluan' => ${JSON.stringify(options.keperluan)},`,
        `'status' => \\App\\Models\\PengajuanSurat::STATUS_DITOLAK,`,
        `'catatan_admin' => ${JSON.stringify(options.catatanAdmin)},`,
        `'tanggal_pengajuan' => now()->toDateString(),`,
        `]`,
        `);`,
    ].join('');

    execSync(`php artisan tinker --execute ${JSON.stringify(php)}`, {
        cwd: projectRoot,
        stdio: 'pipe',
    });
}

function seedDiajukanPengajuan(options: {
    email: string;
    namaSurat: string;
    keperluan: string;
    nomorPengajuan: string;
}): void {
    const php = [
        `\\App\\Models\\PengajuanSurat::updateOrCreate(`,
        `['nomor_pengajuan' => ${JSON.stringify(options.nomorPengajuan)}],`,
        `[`,
        `'user_id' => \\App\\Models\\User::where('email', ${JSON.stringify(options.email)})->value('id'),`,
        `'jenis_surat_id' => \\App\\Models\\JenisSurat::where('nama_surat', ${JSON.stringify(options.namaSurat)})->value('id'),`,
        `'keperluan' => ${JSON.stringify(options.keperluan)},`,
        `'status' => \\App\\Models\\PengajuanSurat::STATUS_DIAJUKAN,`,
        `'catatan_admin' => null,`,
        `'tanggal_pengajuan' => now()->toDateString(),`,
        `]`,
        `);`,
    ].join('');

    execSync(`php artisan tinker --execute ${JSON.stringify(php)}`, {
        cwd: projectRoot,
        stdio: 'pipe',
    });
}

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

async function uploadKtpFile(page: import('@playwright/test').Page, filePath: string): Promise<void> {
    await page.locator('[data-test="pengajuan-surat-dokumen-ktp-input"]').setInputFiles(filePath);
    await expect(page.locator('[data-test="pengajuan-surat-dokumen-ktp-preview"]')).toBeVisible({
        timeout: 30_000,
    });
}

async function uploadKkFile(page: import('@playwright/test').Page, filePath: string): Promise<void> {
    await page.locator('[data-test="pengajuan-surat-dokumen-kk-input"]').setInputFiles(filePath);
    await expect(page.locator('[data-test="pengajuan-surat-dokumen-kk-preview"]')).toBeVisible({
        timeout: 30_000,
    });
}

test.describe('US-3.4 Ajukan Ulang Setelah Ditolak', () => {
    test('warga dapat ajukan ulang dari riwayat dengan form terisi dan nomor pengajuan baru', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.ajukan.ulang.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));
        const namaSurat = `Surat Ajukan Ulang E2E ${stamp}`;
        const keperluan = `Keperluan awal ${stamp}`;
        const catatanAdmin = `Dokumen KTP buram, unggah ulang ${stamp}`;
        const nomorLama = `PJ-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-9001`;

        ensureUser({
            email,
            name: 'Warga Ajukan Ulang Happy',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat);
        seedDitolakPengajuan({
            email,
            namaSurat,
            keperluan,
            catatanAdmin,
            nomorPengajuan: nomorLama,
        });

        await loginAs(page, email);
        await page.goto('/riwayat-pengajuan');

        await expect(page.locator('[data-test="riwayat-pengajuan-heading"]')).toBeVisible();
        await expect(page.getByText(catatanAdmin)).toBeVisible();
        await expect(page.getByRole('link', { name: 'Ajukan Ulang' })).toBeVisible();

        await page.getByRole('link', { name: 'Ajukan Ulang' }).click();

        await expect(page.locator('[data-test="pengajuan-surat-catatan-admin-referensi"]')).toBeVisible();
        await expect(page.getByText(catatanAdmin)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-nomor-sebelumnya"]')).toContainText(nomorLama);
        await expect(page.locator('[data-test="pengajuan-surat-keperluan-input"]')).toHaveValue(keperluan);
        await expect(page.locator('[data-test="pengajuan-surat-dokumen-section"]')).toBeVisible();

        await uploadKtpFile(page, path.join(fixturesDir, 'ktp-sample.jpg'));
        await uploadKkFile(page, path.join(fixturesDir, 'kk-sample.png'));
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toBeVisible();
        const nomorBaru = page.locator('[data-test="pengajuan-surat-nomor"]');
        await expect(nomorBaru).toBeVisible();
        await expect(nomorBaru).toHaveText(/^PJ-\d{8}-\d+$/);
        await expect(nomorBaru).not.toHaveText(nomorLama);
    });

    test('tombol Ajukan Ulang tidak muncul untuk pengajuan berstatus diajukan', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.ajukan.ulang.diajukan.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 1);
        const namaSurat = `Surat Non Ditolak E2E ${stamp}`;
        const nomorDiajukan = `PJ-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-9002`;

        ensureUser({
            email,
            name: 'Warga Non Ditolak',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, '- Fotokopi KTP');
        seedDiajukanPengajuan({
            email,
            namaSurat,
            keperluan: 'Keperluan masih diajukan',
            nomorPengajuan: nomorDiajukan,
        });

        await loginAs(page, email);
        await page.goto('/riwayat-pengajuan');

        await expect(page.getByText(nomorDiajukan)).toBeVisible();
        await expect(page.getByRole('link', { name: 'Ajukan Ulang' })).toHaveCount(0);
    });

    test('ajukan ulang gagal jika dokumen wajib belum diunggah ulang', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.ajukan.ulang.fail.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 2);
        const namaSurat = `Surat Ajukan Ulang Fail ${stamp}`;
        const nomorLama = `PJ-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-9003`;

        ensureUser({
            email,
            name: 'Warga Ajukan Ulang Fail',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, '- Fotokopi KTP');
        seedDitolakPengajuan({
            email,
            namaSurat,
            keperluan: 'Keperluan perlu perbaikan dokumen',
            catatanAdmin: 'Unggah KTP yang lebih jelas',
            nomorPengajuan: nomorLama,
        });

        await loginAs(page, email);
        await page.goto('/riwayat-pengajuan');
        await page.getByRole('link', { name: 'Ajukan Ulang' }).click();

        await expect(page.locator('[data-test="pengajuan-surat-keperluan-input"]')).not.toHaveValue('');
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.getByText(/Fotokopi KTP wajib diunggah/i)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });
});
