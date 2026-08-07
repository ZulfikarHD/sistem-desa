import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * Pengaturan Desa — admin edit identitas kop.
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
}): void {
    const php = [
        `\\App\\Models\\User::updateOrCreate(`,
        `['email' => ${JSON.stringify(options.email)}],`,
        `[`,
        `'name' => ${JSON.stringify(options.name)},`,
        `'nik' => ${JSON.stringify(options.nik)},`,
        `'no_telepon' => '081234567890',`,
        `'alamat' => 'Jl. E2E Pengaturan Desa',`,
        `'role' => ${JSON.stringify(options.role)},`,
        `'password' => 'password',`,
        `'email_verified_at' => now(),`,
        `]`,
        `);`,
    ].join('');
    runTinker(php);
}

async function loginAs(page: import('@playwright/test').Page, email: string): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill('password');
    await page.locator('[data-test="login-button"]').click();
}

test.describe('Pengaturan Desa', () => {
    test('admin dapat membuka sidebar dan menyimpan nama desa', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.pengaturan.${stamp}@example.com`;

        ensureUser({
            email: adminEmail,
            name: 'E2E Admin Pengaturan',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });

        await loginAs(page, adminEmail);
        await expect(page.locator('[data-test="sidebar-pengaturan-desa"]')).toBeVisible();
        await page.locator('[data-test="sidebar-pengaturan-desa"]').click();
        await expect(page).toHaveURL(/\/admin\/pengaturan-desa/);
        await expect(page.locator('[data-test="pengaturan-desa-heading"]')).toBeVisible();

        const nama = `Desa E2E ${stamp}`;
        await page.locator('[data-test="pengaturan-desa-nama"]').fill(nama);
        await page.locator('[data-test="pengaturan-desa-simpan"]').click();

        await expect(page.getByText(/berhasil disimpan/i)).toBeVisible({ timeout: 10_000 });

        const saved = runTinker(`echo \\App\\Models\\PengaturanDesa::query()->value('nama_desa');`).trim();
        expect(saved).toBe(nama);
    });

    test('warga mendapat 403 pada pengaturan desa', async ({ page }) => {
        const stamp = Date.now();
        const wargaEmail = `warga.pengaturan.${stamp}@example.com`;

        ensureUser({
            email: wargaEmail,
            name: 'E2E Warga Pengaturan',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });

        await loginAs(page, wargaEmail);
        const response = await page.goto('/admin/pengaturan-desa');
        expect(response?.status()).toBe(403);
    });
});
