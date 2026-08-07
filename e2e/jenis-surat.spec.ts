import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-2.1 + US-9.1 — Kelola Data Jenis Surat & Persyaratan Terstruktur (Admin)
 * Happy path: list, search, create, edit, structured persyaratan, template, pratinjau.
 * Edge/failure: guest redirect, warga 403, validasi nama & minimal satu syarat.
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

function uniqueNik(suffix: number): string {
    return `3204040404${String(suffix).padStart(6, '0')}`;
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
        `'alamat' => 'Jl. E2E Jenis Surat No. 1',`,
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

function ensureJenisSurat(namaSurat: string, deskripsi = 'Deskripsi e2e'): void {
    // Hindari variabel $ di string --execute (bash meng-expand meskipun di JSON.stringify).
    const php = [
        `\\App\\Models\\JenisSurat::query()->updateOrCreate(`,
        `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
        `[`,
        `'deskripsi' => ${JSON.stringify(deskripsi)},`,
        `'persyaratan_dokumen' => "- Fotokopi KTP\\n- Fotokopi KK",`,
        `]`,
        `)->syncPersyaratan([`,
        `['nama' => 'Fotokopi KTP', 'cara_pemenuhan' => 'unggah', 'is_wajib' => true, 'urutan' => 0],`,
        `['nama' => 'Fotokopi KK', 'cara_pemenuhan' => 'unggah', 'is_wajib' => true, 'urutan' => 1],`,
        `]);`,
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

async function fillPersyaratanRow(
    page: import('@playwright/test').Page,
    index: number,
    options: { nama: string; cara: 'unggah' | 'bawa_kantor' | 'info'; wajib?: boolean },
): Promise<void> {
    await page.locator(`[data-test="jenis-surat-persyaratan-nama-${index}"]`).fill(options.nama);
    await page.locator(`[data-test="jenis-surat-persyaratan-cara-${index}-${options.cara}"]`).click();

    if (options.cara === 'unggah') {
        const wajib = options.wajib ?? true;
        await page
            .locator(`[data-test="jenis-surat-persyaratan-wajib-${index}-${wajib ? 'true' : 'false'}"]`)
            .click();
    }
}

test.describe('US-2.1 / US-9.1 Kelola Data Jenis Surat', () => {
    test('guest yang mengakses halaman jenis surat diarahkan ke login', async ({ page }) => {
        await page.goto('/admin/jenis-surat');
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('input[name="email"]')).toBeVisible();
    });

    test('warga yang mengakses halaman jenis surat mendapat 403', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.jenis.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));

        ensureUser({
            email,
            name: 'Warga Jenis Surat Forbid',
            role: 'warga',
            nik,
        });

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/dashboard$/);

        const response = await page.goto('/admin/jenis-surat');
        expect(response?.status()).toBe(403);
        await expect(page.locator('[data-test="jenis-surat-heading"]')).toHaveCount(0);
        await expect(page.getByText(/403|Forbidden|tidak diizinkan|Unauthorized/i).first()).toBeVisible();
    });

    test('admin dapat membuka daftar jenis surat dan menambah data dengan syarat terstruktur', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.jenis.create.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 1);
        const namaSurat = `Surat Domisili E2E ${stamp}`;

        ensureUser({
            email,
            name: 'Admin Jenis Create',
            role: 'admin',
            nik,
        });

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        await page.goto('/admin/jenis-surat');
        await expect(page.locator('[data-test="jenis-surat-heading"]')).toBeVisible();

        await page.locator('[data-test="jenis-surat-create-button"]').click();
        await expect(page).toHaveURL(/\/admin\/jenis-surat\/create/);
        await expect(page.locator('[data-test="jenis-surat-form-title"]')).toContainText('Tambah Jenis Surat');

        await page.locator('[data-test="jenis-surat-nama-input"]').fill(namaSurat);
        await page.locator('[data-test="jenis-surat-deskripsi-input"]').fill('Untuk keterangan tempat tinggal');
        await fillPersyaratanRow(page, 0, { nama: 'Fotokopi KTP', cara: 'unggah', wajib: true });
        await page.locator('[data-test="jenis-surat-add-persyaratan"]').click();
        await fillPersyaratanRow(page, 1, { nama: 'Surat pengantar RT/RW', cara: 'bawa_kantor' });

        await expect(page.locator('[data-test="jenis-surat-pratinjau-badge-0"]')).toContainText('Wajib diunggah');
        await expect(page.locator('[data-test="jenis-surat-pratinjau-badge-1"]')).toContainText('Bawa ke kantor');

        await page.locator('[data-test="jenis-surat-save-button"]').click();

        await expect(page).toHaveURL(/\/admin\/jenis-surat$/);
        await expect(page.getByText(namaSurat)).toBeVisible();
    });

    test('admin dapat memakai template cepat dan melihat pratinjau badge', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.jenis.template.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 8);
        const namaSurat = `Surat Template E2E ${stamp}`;

        ensureUser({
            email,
            name: 'Admin Jenis Template',
            role: 'admin',
            nik,
        });

        await loginAs(page, email);
        await page.goto('/admin/jenis-surat/create');
        await expect(page.locator('[data-test="jenis-surat-form-title"]')).toContainText('Tambah Jenis Surat');

        await page.locator('[data-test="jenis-surat-nama-input"]').fill(namaSurat);
        await page.locator('[data-test="jenis-surat-template-domisili"]').click();

        await expect(page.locator('[data-test="jenis-surat-persyaratan-nama-0"]')).toHaveValue('Fotokopi KTP');
        await expect(page.locator('[data-test="jenis-surat-persyaratan-nama-1"]')).toHaveValue(
            'Fotokopi Kartu Keluarga (KK)',
        );
        await expect(page.locator('[data-test="jenis-surat-persyaratan-nama-2"]')).toHaveValue(
            'Surat pengantar RT/RW',
        );
        await expect(page.locator('[data-test="jenis-surat-pratinjau-badge-0"]')).toContainText('Wajib diunggah');
        await expect(page.locator('[data-test="jenis-surat-pratinjau-badge-2"]')).toContainText('Bawa ke kantor');

        await page.locator('[data-test="jenis-surat-save-button"]').click();
        await expect(page).toHaveURL(/\/admin\/jenis-surat$/);
        await expect(page.getByText(namaSurat)).toBeVisible();
    });

    test('admin dapat mencari jenis surat', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.jenis.search.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 2);
        const targetName = `Surat Cari Target ${stamp}`;
        const otherName = `Surat Cari Lain ${stamp}`;

        ensureUser({
            email,
            name: 'Admin Jenis Search',
            role: 'admin',
            nik,
        });
        ensureJenisSurat(targetName, 'Deskripsi target pencarian');
        ensureJenisSurat(otherName, 'Deskripsi lain');

        await loginAs(page, email);
        await page.goto('/admin/jenis-surat');

        await page.locator('[data-test="jenis-surat-search"]').fill('Target');
        await expect(page.getByText(targetName)).toBeVisible();
        await expect(page.getByText(otherName)).toHaveCount(0);
    });

    test('admin dapat mengubah jenis surat', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.jenis.edit.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 3);
        const oldName = `Surat Edit Lama ${stamp}`;
        const newName = `Surat Edit Baru ${stamp}`;

        ensureUser({
            email,
            name: 'Admin Jenis Edit',
            role: 'admin',
            nik,
        });
        ensureJenisSurat(oldName);

        await loginAs(page, email);
        await page.goto('/admin/jenis-surat');

        await page.locator('[data-test="jenis-surat-search"]').fill(oldName);
        await expect(page.getByText(oldName)).toBeVisible();

        await page.locator('[data-test^="jenis-surat-edit-"]').first().click();
        await expect(page).toHaveURL(/\/admin\/jenis-surat\/\d+\/edit/);
        await expect(page.locator('[data-test="jenis-surat-form-title"]')).toContainText('Ubah Jenis Surat');

        const namaInput = page.locator('[data-test="jenis-surat-nama-input"]');
        await namaInput.clear();
        await namaInput.fill(newName);
        await page.locator('[data-test="jenis-surat-save-button"]').click();

        await expect(page).toHaveURL(/\/admin\/jenis-surat$/);
        await page.locator('[data-test="jenis-surat-search"]').fill(newName);
        await expect(page.getByText(newName)).toBeVisible();
        await expect(page.getByText(oldName)).toHaveCount(0);
    });

    test('validasi gagal jika nama surat kosong', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.jenis.required.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 4);

        ensureUser({
            email,
            name: 'Admin Jenis Required',
            role: 'admin',
            nik,
        });

        await loginAs(page, email);
        await page.goto('/admin/jenis-surat/create');

        await expect(page.locator('[data-test="jenis-surat-form-title"]')).toBeVisible();
        await page.locator('[data-test="jenis-surat-nama-input"]').fill('');
        await page.locator('[data-test="jenis-surat-save-button"]').click();

        await expect(page.getByText(/Nama surat wajib diisi/i)).toBeVisible();
        await expect(page).toHaveURL(/\/admin\/jenis-surat\/create/);
        await expect(page.locator('[data-test="jenis-surat-form-title"]')).toBeVisible();
    });

    test('validasi gagal jika nama surat duplikat', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.jenis.unique.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 5);
        const existingName = `Surat Unik E2E ${stamp}`;

        ensureUser({
            email,
            name: 'Admin Jenis Unique',
            role: 'admin',
            nik,
        });
        ensureJenisSurat(existingName);

        await loginAs(page, email);
        await page.goto('/admin/jenis-surat/create');

        await page.locator('[data-test="jenis-surat-nama-input"]').fill(existingName);
        await fillPersyaratanRow(page, 0, { nama: 'Fotokopi KTP', cara: 'unggah', wajib: true });
        await page.locator('[data-test="jenis-surat-save-button"]').click();

        await expect(page.getByText(/Nama surat sudah digunakan/i)).toBeVisible();
        await expect(page).toHaveURL(/\/admin\/jenis-surat\/create/);
    });

    test('validasi gagal jika nama syarat kosong', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.jenis.persyaratan.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 6);

        ensureUser({
            email,
            name: 'Admin Jenis Persyaratan',
            role: 'admin',
            nik,
        });

        await loginAs(page, email);
        await page.goto('/admin/jenis-surat/create');

        await page.locator('[data-test="jenis-surat-nama-input"]').fill(`Surat Tanpa Nama Syarat ${stamp}`);
        await page.locator('[data-test="jenis-surat-persyaratan-nama-0"]').fill('');
        await page.locator('[data-test="jenis-surat-save-button"]').click();

        await expect(page.getByText(/Nama syarat wajib diisi/i)).toBeVisible();
        await expect(page).toHaveURL(/\/admin\/jenis-surat\/create/);
    });

    test('admin dapat soft delete lalu restore dan hard delete', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.jenis.delete.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 7);
        const namaSurat = `Surat Hapus E2E ${stamp}`;

        ensureUser({
            email,
            name: 'Admin Jenis Delete',
            role: 'admin',
            nik,
        });
        ensureJenisSurat(namaSurat);

        await loginAs(page, email);
        await page.goto('/admin/jenis-surat');

        await page.locator('[data-test="jenis-surat-search"]').fill(namaSurat);
        await expect(page.getByText(namaSurat)).toBeVisible();

        page.once('dialog', (dialog) => dialog.accept());
        await page.getByRole('button', { name: 'Arsipkan' }).first().click();
        await expect(page.getByText(namaSurat)).toHaveCount(0);

        await page.locator('[data-test="jenis-surat-trash-toggle"]').click();
        await page.locator('[data-test="jenis-surat-search"]').fill(namaSurat);
        await expect(page.getByText(namaSurat)).toBeVisible();

        await page.getByRole('button', { name: 'Pulihkan' }).first().click();
        await expect(page.getByText(namaSurat)).toHaveCount(0);

        await page.locator('[data-test="jenis-surat-trash-toggle"]').click();
        await page.locator('[data-test="jenis-surat-search"]').fill(namaSurat);
        await expect(page.getByText(namaSurat)).toBeVisible();

        page.once('dialog', (dialog) => dialog.accept());
        await page.getByRole('button', { name: 'Arsipkan' }).first().click();

        await page.locator('[data-test="jenis-surat-trash-toggle"]').click();
        await page.locator('[data-test="jenis-surat-search"]').fill(namaSurat);
        await expect(page.getByText(namaSurat)).toBeVisible();

        await page.getByRole('button', { name: 'Hapus Permanen' }).first().click();
        await expect(page.getByText('Hapus permanen?')).toBeVisible();
        await page.locator('[data-test="jenis-surat-force-delete-confirm"]').click();

        await expect(page.getByText(namaSurat)).toHaveCount(0);
    });
});
