import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-2.2 — Tampilan Persyaratan Dokumen untuk Warga
 * Happy path: list, detail modal, search.
 * Edge/failure: guest redirect, admin 403, empty state, soft-deleted hidden.
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

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
        `'alamat' => 'Jl. E2E Persyaratan Dokumen No. 1',`,
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
    deskripsi = 'Deskripsi e2e persyaratan',
    persyaratan = '- Fotokopi KTP\n- Fotokopi KK',
): void {
    const php = [
        `\\App\\Models\\JenisSurat::query()->updateOrCreate(`,
        `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
        `[`,
        `'deskripsi' => ${JSON.stringify(deskripsi)},`,
        `'persyaratan_dokumen' => ${JSON.stringify(persyaratan)},`,
        `]`,
        `)->syncPersyaratan(`,
        `\\App\\Models\\JenisSuratPersyaratan::parseFromFreeText(${JSON.stringify(persyaratan)}`,
        `)`,
        `);`,
    ].join('');

    execSync(`php artisan tinker --execute ${JSON.stringify(php)}`, {
        cwd: projectRoot,
        stdio: 'pipe',
    });
}

function softDeleteJenisSurat(namaSurat: string): void {
    const php = [
        `\\App\\Models\\JenisSurat::where('nama_surat', ${JSON.stringify(namaSurat)})`,
        `->get()->each->delete();`,
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

test.describe('US-2.2 Tampilan Persyaratan Dokumen untuk Warga', () => {
    test('warga tidak melihat CTA guest Daftar/Login untuk Mengajukan', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.persyaratan.cta.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));

        ensureUser({
            email,
            name: 'Warga Persyaratan CTA',
            role: 'warga',
            nik,
        });

        await loginAs(page, email);
        await page.goto('/persyaratan-dokumen');

        await expect(page.locator('[data-test="persyaratan-dokumen-heading"]')).toBeVisible();
        await expect(page.locator('[data-test="persyaratan-dokumen-guest-cta"]')).toHaveCount(0);
    });

    test('admin dapat membuka halaman persyaratan dokumen publik', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.persyaratan.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));
        const namaSurat = `Surat Admin Lihat Persyaratan ${stamp}`;

        ensureUser({
            email,
            name: 'Admin Persyaratan View',
            role: 'admin',
            nik,
        });
        ensureJenisSurat(namaSurat, 'Deskripsi untuk admin view');

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        const response = await page.goto('/persyaratan-dokumen');
        expect(response?.status()).toBe(200);
        await expect(page.locator('[data-test="persyaratan-dokumen-heading"]')).toBeVisible();
        await expect(page.locator('[data-test="persyaratan-dokumen-guest-cta"]')).toHaveCount(0);
        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(namaSurat);
        await expect(page.getByText(namaSurat)).toBeVisible();
    });

    test('warga dapat melihat daftar jenis surat beserta deskripsi dan persyaratan', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.persyaratan.list.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 1);
        const namaSurat = `Surat Domisili Persyaratan ${stamp}`;
        const deskripsi = `Deskripsi list unik ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Persyaratan List',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, deskripsi, '- Fotokopi KTP\n- Fotokopi KK');

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/dashboard$/);

        await page.goto('/persyaratan-dokumen');
        await expect(page.locator('[data-test="persyaratan-dokumen-heading"]')).toBeVisible();
        await expect(page.getByText(namaSurat)).toBeVisible();
        await expect(page.getByText(deskripsi)).toBeVisible();
        await expect(page.getByText(/Fotokopi KTP/).first()).toBeVisible();
    });

    test('warga dapat membuka detail per jenis surat', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.persyaratan.detail.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 2);
        const namaSurat = `Surat Detail Persyaratan ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Persyaratan Detail',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(
            namaSurat,
            'Deskripsi detail lengkap untuk e2e',
            '- KTP asli\n- KK asli\n- Surat pengantar RT/RW',
        );

        await loginAs(page, email);
        await page.goto('/persyaratan-dokumen');

        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(namaSurat);
        await expect(page.getByText(namaSurat)).toBeVisible();

        await page.getByRole('button', { name: 'Lihat Detail' }).first().click();
        await expect(page.locator('[data-test="persyaratan-dokumen-detail-title"]')).toBeVisible();
        await expect(page.locator('[data-test="persyaratan-dokumen-detail-title"]')).toContainText(namaSurat);
        await expect(page.locator('[data-test="persyaratan-dokumen-detail-deskripsi"]')).toContainText(
            'Deskripsi detail lengkap untuk e2e',
        );
        await expect(page.locator('[data-test="persyaratan-dokumen-detail-persyaratan"]')).toContainText('KTP asli');
        await expect(page.locator('[data-test="persyaratan-dokumen-detail-persyaratan"]')).toContainText(
            'Surat pengantar RT/RW',
        );
        await expect(page.getByText('Wajib diunggah').first()).toBeVisible();
        await expect(page.getByText('Bawa ke kantor').first()).toBeVisible();

        await page.locator('[data-test="persyaratan-dokumen-detail-close"]').click();
        await expect(page.locator('[data-test="persyaratan-dokumen-detail-title"]')).toBeHidden();
    });

    test('warga dapat mencari jenis surat di halaman persyaratan', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.persyaratan.search.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 3);
        const targetName = `Surat Cari Target Persyaratan ${stamp}`;
        const otherName = `Surat Cari Lain Persyaratan ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Persyaratan Search',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(targetName, 'Deskripsi target pencarian warga');
        ensureJenisSurat(otherName, 'Deskripsi lain warga');

        await loginAs(page, email);
        await page.goto('/persyaratan-dokumen');

        await page.locator('[data-test="persyaratan-dokumen-search"]').fill('Target');
        await expect(page.getByText(targetName)).toBeVisible();
        await expect(page.getByText(otherName)).toHaveCount(0);
    });

    test('jenis surat terarsip tidak tampil untuk warga', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.persyaratan.arsip.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 4);
        const activeName = `Surat Aktif Persyaratan ${stamp}`;
        const archivedName = `Surat Arsip Persyaratan ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Persyaratan Arsip',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(activeName);
        ensureJenisSurat(archivedName);
        softDeleteJenisSurat(archivedName);

        await loginAs(page, email);
        await page.goto('/persyaratan-dokumen');

        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(`Persyaratan ${stamp}`);
        await expect(page.getByText(activeName)).toBeVisible();
        await expect(page.getByText(archivedName)).toHaveCount(0);
    });

    test('halaman persyaratan responsif di viewport smartphone', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.persyaratan.mobile.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 5);
        const namaSurat = `Surat Mobile Persyaratan ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Persyaratan Mobile',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, 'Deskripsi tampilan mobile');

        await page.setViewportSize({ width: 390, height: 844 });
        await loginAs(page, email);
        await page.goto('/persyaratan-dokumen');

        await expect(page.locator('[data-test="persyaratan-dokumen-heading"]')).toBeVisible();
        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(namaSurat);
        await expect(page.getByText(namaSurat)).toBeVisible();
        await expect(page.getByRole('button', { name: 'Lihat Detail' }).first()).toBeVisible();
    });

    test('pencarian tanpa hasil menampilkan empty state', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.persyaratan.empty.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 6);

        ensureUser({
            email,
            name: 'Warga Persyaratan Empty',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(`Surat Ada Persyaratan ${stamp}`);

        await loginAs(page, email);
        await page.goto('/persyaratan-dokumen');

        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(`TidakAdaHasil${stamp}`);
        await expect(page.locator('[data-test="persyaratan-dokumen-empty"]')).toBeVisible();
        await expect(page.getByText(/Tidak ada hasil untuk pencarian Anda/i)).toBeVisible();
    });
});
