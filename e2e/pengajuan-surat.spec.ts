import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-3.1 — Form Pengajuan Surat Keterangan (Warga)
 * US-3.2 — Unggah Dokumen Persyaratan (KTP/KK)
 * US-3.3 — Validasi Kelengkapan Pengajuan (dokumen wajib)
 * Happy path: isi form, upload dokumen, submit, nomor pengajuan otomatis.
 * Edge/failure: guest redirect, admin 403, validasi jenis surat & keperluan wajib,
 * validasi format/ukuran file upload.
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
        `'alamat' => 'Jl. E2E Pengajuan Surat No. 1',`,
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
    deskripsi = 'Deskripsi e2e pengajuan',
): void {
    const php = [
        `\\App\\Models\\JenisSurat::updateOrCreate(`,
        `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
        `[`,
        `'deskripsi' => ${JSON.stringify(deskripsi)},`,
        `'persyaratan_dokumen' => ${JSON.stringify(persyaratanDokumen)},`,
        `]`,
        `);`,
    ].join('');

    execSync(`php artisan tinker --execute ${JSON.stringify(php)}`, {
        cwd: projectRoot,
        stdio: 'pipe',
    });
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

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

async function selectJenisSuratByName(page: import('@playwright/test').Page, namaSurat: string): Promise<void> {
    await page.getByRole('combobox', { name: 'Jenis Surat' }).selectOption({ label: namaSurat });
}

test.describe('US-3.1 Form Pengajuan Surat Keterangan', () => {
    test('guest yang mengakses halaman pengajuan diarahkan ke login', async ({ page }) => {
        await page.goto('/pengajuan-surat');
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('input[name="email"]')).toBeVisible();
    });

    test('admin yang mengakses halaman pengajuan mendapat 403', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.pengajuan.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));

        ensureUser({
            email,
            name: 'Admin Pengajuan Forbid',
            role: 'admin',
            nik,
        });

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        const response = await page.goto('/pengajuan-surat');
        expect(response?.status()).toBe(403);
        await expect(page.locator('[data-test="pengajuan-surat-heading"]')).toHaveCount(0);
    });

    test('warga dapat mengajukan surat dan mendapat nomor pengajuan otomatis', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.pengajuan.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 1);
        const namaSurat = `Surat Pengajuan E2E ${stamp}`;
        const keperluan = `Keperluan administrasi bank ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Pengajuan Happy',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat);

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/dashboard$/);

        await page.goto('/pengajuan-surat');
        await expect(page.locator('[data-test="pengajuan-surat-heading"]')).toBeVisible();

        await selectJenisSuratByName(page, namaSurat);
        await expect(page.locator('[data-test="pengajuan-surat-dokumen-section"]')).toBeVisible();

        await uploadKtpFile(page, path.join(fixturesDir, 'ktp-sample.jpg'));
        await uploadKkFile(page, path.join(fixturesDir, 'kk-sample.png'));

        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill(keperluan);
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toBeVisible();
        const nomor = page.locator('[data-test="pengajuan-surat-nomor"]');
        await expect(nomor).toBeVisible();
        await expect(nomor).toHaveText(/^PJ-\d{8}-\d{4}$/);
    });

    test('validasi gagal jika jenis surat tidak dipilih', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.pengajuan.required.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 2);

        ensureUser({
            email,
            name: 'Warga Pengajuan Required',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(`Surat Validasi Jenis ${stamp}`);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill('Keperluan tanpa jenis surat');
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.getByText(/Jenis surat wajib dipilih/i)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });

    test('validasi gagal jika keperluan kosong', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.pengajuan.keperluan.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 3);
        const namaSurat = `Surat Validasi Keperluan ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Pengajuan Keperluan',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await selectJenisSuratByName(page, namaSurat);
        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill('');
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.getByText(/Keperluan wajib diisi/i)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });
});

test.describe('US-3.2 Unggah Dokumen Persyaratan', () => {
    test('area unggah KTP/KK muncul sesuai persyaratan jenis surat terpilih', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.dokumen.section.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 10);
        const namaSurat = `Surat Dokumen Section ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Dokumen Section',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, '- Fotokopi KTP\n- Fotokopi KK');

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await expect(page.locator('[data-test="pengajuan-surat-dokumen-section"]')).toHaveCount(0);

        await selectJenisSuratByName(page, namaSurat);

        await expect(page.locator('[data-test="pengajuan-surat-dokumen-section"]')).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-dokumen-ktp-input"]')).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-dokumen-kk-input"]')).toBeVisible();
    });

    test('warga dapat melihat preview dokumen sebelum submit final', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.dokumen.preview.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 11);
        const namaSurat = `Surat Dokumen Preview ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Dokumen Preview',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, '- Fotokopi KTP');

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await selectJenisSuratByName(page, namaSurat);

        await uploadKtpFile(page, path.join(fixturesDir, 'ktp-sample.jpg'));
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });

    test('validasi gagal jika format file tidak didukung', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.dokumen.invalid.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 12);
        const namaSurat = `Surat Dokumen Invalid ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Dokumen Invalid',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, '- Fotokopi KTP');

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await selectJenisSuratByName(page, namaSurat);
        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill('Keperluan dengan file invalid');

        await page.locator('[data-test="pengajuan-surat-dokumen-ktp-input"]').setInputFiles(
            path.join(fixturesDir, 'invalid-sample.txt'),
        );

        // Tunggu Livewire selesai memproses unggahan sementara (tidak ada preview untuk file invalid).
        await page.waitForTimeout(2000);

        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.getByText(/Format KTP harus JPG, PNG, atau PDF/i)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });
});

test.describe('US-3.3 Validasi Kelengkapan Pengajuan', () => {
    test('validasi gagal jika dokumen KTP wajib belum diunggah', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.kelengkapan.ktp.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 20);
        const namaSurat = `Surat Kelengkapan KTP ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Kelengkapan KTP',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, '- Fotokopi KTP');

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await selectJenisSuratByName(page, namaSurat);
        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill('Keperluan tanpa unggah KTP');
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.getByText(/Fotokopi KTP wajib diunggah/i)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });

    test('validasi gagal jika KK wajib belum diunggah meskipun KTP sudah diunggah', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.kelengkapan.kk.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 21);
        const namaSurat = `Surat Kelengkapan KK ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Kelengkapan KK',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, '- Fotokopi KTP\n- Fotokopi KK');

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await selectJenisSuratByName(page, namaSurat);
        await uploadKtpFile(page, path.join(fixturesDir, 'ktp-sample.jpg'));
        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill('Keperluan hanya KTP, KK belum');
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.getByText(/Fotokopi Kartu Keluarga \(KK\) wajib diunggah/i)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });

    test('pengajuan tersimpan dengan status diajukan hanya jika semua dokumen wajib lengkap', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.kelengkapan.lengkap.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 22);
        const namaSurat = `Surat Kelengkapan Lengkap ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Kelengkapan Lengkap',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, '- Fotokopi KTP\n- Fotokopi KK');

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await selectJenisSuratByName(page, namaSurat);
        await uploadKtpFile(page, path.join(fixturesDir, 'ktp-sample.jpg'));
        await uploadKkFile(page, path.join(fixturesDir, 'kk-sample.png'));
        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill(`Keperluan lengkap ${stamp}`);
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-nomor"]')).toHaveText(/^PJ-\d{8}-\d{4}$/);
    });
});
