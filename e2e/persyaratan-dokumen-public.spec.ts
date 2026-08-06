import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-2.3 — Akses Publik ke Informasi Persyaratan Dokumen
 * Happy path: guest list, CTA daftar/login, detail read-only.
 * Edge/failure: soft-deleted hidden, no pengajuan submit, welcome link.
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

function ensureJenisSurat(
    namaSurat: string,
    deskripsi = 'Deskripsi e2e publik persyaratan',
    persyaratan = '- Fotokopi KTP\n- Fotokopi KK',
): void {
    const php = [
        `\\App\\Models\\JenisSurat::updateOrCreate(`,
        `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
        `[`,
        `'deskripsi' => ${JSON.stringify(deskripsi)},`,
        `'persyaratan_dokumen' => ${JSON.stringify(persyaratan)},`,
        `]`,
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

test.describe('US-2.3 Akses Publik Persyaratan Dokumen', () => {
    test('guest dapat mengakses persyaratan dokumen tanpa login', async ({ page }) => {
        const stamp = Date.now();
        const namaSurat = `Surat Publik Akses ${stamp}`;
        const deskripsi = `Deskripsi publik unik ${stamp}`;

        ensureJenisSurat(namaSurat, deskripsi, '- Fotokopi KTP\n- Fotokopi KK');

        const response = await page.goto('/persyaratan-dokumen');
        expect(response?.status()).toBe(200);
        await expect(page).toHaveURL(/\/persyaratan-dokumen/);
        await expect(page.locator('[data-test="persyaratan-dokumen-heading"]')).toBeVisible();
        await expect(page.getByText(namaSurat)).toBeVisible();
        await expect(page.getByText(deskripsi)).toBeVisible();
        await expect(page.getByText(/Fotokopi KTP/).first()).toBeVisible();
    });

    test('guest melihat CTA Daftar/Login untuk Mengajukan', async ({ page }) => {
        await page.goto('/persyaratan-dokumen');

        await expect(page.locator('[data-test="persyaratan-dokumen-guest-cta"]')).toBeVisible();
        await expect(page.getByText('Daftar/Login untuk Mengajukan')).toBeVisible();
        await expect(page.locator('[data-test="persyaratan-dokumen-cta-register"]')).toBeVisible();
        await expect(page.locator('[data-test="persyaratan-dokumen-cta-login"]')).toBeVisible();

        await page.locator('[data-test="persyaratan-dokumen-cta-login"]').click();
        await expect(page).toHaveURL(/\/login/);
        await expect(page.getByText('Masuk ke akun Anda')).toBeVisible();
    });

    test('CTA Daftar dari persyaratan mengarah ke registrasi', async ({ page }) => {
        await page.goto('/persyaratan-dokumen');

        await page.locator('[data-test="persyaratan-dokumen-cta-register"]').click();
        await expect(page).toHaveURL(/\/register/);
        await expect(page.getByText('Registrasi Akun Warga')).toBeVisible();
    });

    test('guest dapat membuka detail tanpa tombol submit pengajuan', async ({ page }) => {
        const stamp = Date.now();
        const namaSurat = `Surat Publik Detail ${stamp}`;

        ensureJenisSurat(
            namaSurat,
            'Deskripsi detail publik e2e',
            '- KTP asli\n- KK asli\n- Surat pengantar RT/RW',
        );

        await page.goto('/persyaratan-dokumen');
        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(namaSurat);
        await expect(page.getByText(namaSurat)).toBeVisible();

        await page.getByRole('button', { name: 'Lihat Detail' }).first().click();
        await expect(page.locator('[data-test="persyaratan-dokumen-detail-title"]')).toBeVisible();
        await expect(page.locator('[data-test="persyaratan-dokumen-detail-title"]')).toContainText(namaSurat);
        await expect(page.locator('[data-test="persyaratan-dokumen-detail-persyaratan"]')).toContainText('KTP asli');

        await expect(page.getByRole('button', { name: /ajukan|submit|kirim pengajuan/i })).toHaveCount(0);
        await expect(page.locator('form[action*="pengajuan"]')).toHaveCount(0);

        await page.locator('[data-test="persyaratan-dokumen-detail-close"]').click();
        await expect(page.locator('[data-test="persyaratan-dokumen-detail-title"]')).toBeHidden();
    });

    test('jenis surat terarsip tidak tampil untuk guest', async ({ page }) => {
        const stamp = Date.now();
        const activeName = `Surat Aktif Publik ${stamp}`;
        const archivedName = `Surat Arsip Publik ${stamp}`;

        ensureJenisSurat(activeName);
        ensureJenisSurat(archivedName);
        softDeleteJenisSurat(archivedName);

        await page.goto('/persyaratan-dokumen');
        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(`Publik ${stamp}`);
        await expect(page.getByText(activeName)).toBeVisible();
        await expect(page.getByText(archivedName)).toHaveCount(0);
    });

    test('tautan Lihat Persyaratan Dokumen dari beranda membuka halaman publik', async ({ page }) => {
        await page.goto('/');

        await page.locator('[data-test="welcome-persyaratan-dokumen"]').click();
        await expect(page).toHaveURL(/\/persyaratan-dokumen/);
        await expect(page.locator('[data-test="persyaratan-dokumen-heading"]')).toBeVisible();
        await expect(page.locator('[data-test="persyaratan-dokumen-guest-cta"]')).toBeVisible();
    });

    test('pencarian tanpa hasil menampilkan empty state untuk guest', async ({ page }) => {
        const stamp = Date.now();

        await page.goto('/persyaratan-dokumen');
        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(`TidakAdaHasilPublik${stamp}`);
        await expect(page.locator('[data-test="persyaratan-dokumen-empty"]')).toBeVisible();
        await expect(page.getByText(/Tidak ada hasil untuk pencarian Anda/i)).toBeVisible();
    });
});
