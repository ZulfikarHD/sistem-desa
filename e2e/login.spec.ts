import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-1.2 — Login Berbasis Role
 * Mencakup happy path warga/admin + skenario kredensial salah + logout.
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

function uniqueNik(suffix: number): string {
    return `3202020202${String(suffix).padStart(6, '0')}`;
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
        `'alamat' => 'Jl. E2E Test No. 1',`,
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

test.describe('US-1.2 Login Berbasis Role', () => {
    test('halaman login menampilkan form email dan password', async ({ page }) => {
        await page.goto('/login');

        await expect(page.getByText('Masuk ke akun Anda')).toBeVisible();
        await expect(page.locator('input[name="email"]')).toBeVisible();
        await expect(page.locator('input[name="password"]')).toBeVisible();
        await expect(page.locator('[data-test="login-button"]')).toBeVisible();
    });

    test('warga berhasil login dan diarahkan ke Dashboard Warga', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.login.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));

        ensureUser({
            email,
            name: 'Warga Login E2E',
            role: 'warga',
            nik,
        });

        await page.goto('/login');
        await page.locator('input[name="email"]').fill(email);
        await page.locator('input[name="password"]').fill('password');
        await page.locator('[data-test="login-button"]').click();

        await expect(page).toHaveURL(/\/dashboard$/);
        await expect(page.locator('[data-test="dashboard-warga-heading"]')).toBeVisible();
        await expect(page.getByText(/Dashboard Warga/i)).toBeVisible();
    });

    test('admin berhasil login dan diarahkan ke Dashboard Admin', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.login.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 1);

        ensureUser({
            email,
            name: 'Admin Login E2E',
            role: 'admin',
            nik,
        });

        await page.goto('/login');
        await page.locator('input[name="email"]').fill(email);
        await page.locator('input[name="password"]').fill('password');
        await page.locator('[data-test="login-button"]').click();

        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await expect(page.locator('[data-test="dashboard-admin-heading"]')).toBeVisible();
        await expect(page.getByText(/Dashboard Admin/i)).toBeVisible();
    });

    test('login gagal dengan kredensial salah menampilkan error generik', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.fail.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 2);

        ensureUser({
            email,
            name: 'Warga Fail E2E',
            role: 'warga',
            nik,
        });

        await page.goto('/login');
        await page.locator('input[name="email"]').fill(email);
        await page.locator('input[name="password"]').fill('password-salah');
        await page.locator('[data-test="login-button"]').click();

        await expect(page).toHaveURL(/\/login/);
        // Error generik pada field email (bukan mengungkap password salah secara spesifik)
        await expect(page.getByText(/credentials do not match|tidak cocok|tidak sesuai/i).first()).toBeVisible();
        await expect(page.locator('[data-test="dashboard-warga-heading"]')).toHaveCount(0);
    });

    test('warga dapat logout dari dashboard', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.logout.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 3);

        ensureUser({
            email,
            name: 'Warga Logout E2E',
            role: 'warga',
            nik,
        });

        await page.goto('/login');
        await page.locator('input[name="email"]').fill(email);
        await page.locator('input[name="password"]').fill('password');
        await page.locator('[data-test="login-button"]').click();
        await expect(page).toHaveURL(/\/dashboard$/);

        // Buka menu user (desktop sidebar) lalu logout
        await page.locator('[data-test="sidebar-menu-button"]').click();
        await page.getByRole('menuitem', { name: 'Keluar' }).first().click();

        await expect(page).toHaveURL('/');
        await page.goto('/dashboard');
        await expect(page).toHaveURL(/\/login/);
    });

    test('admin dapat logout dari dashboard admin', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.logout.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 4);

        ensureUser({
            email,
            name: 'Admin Logout E2E',
            role: 'admin',
            nik,
        });

        await page.goto('/login');
        await page.locator('input[name="email"]').fill(email);
        await page.locator('input[name="password"]').fill('password');
        await page.locator('[data-test="login-button"]').click();
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        await page.locator('[data-test="sidebar-menu-button"]').click();
        await page.getByRole('menuitem', { name: 'Keluar' }).first().click();

        await expect(page).toHaveURL('/');
        await page.goto('/admin/dashboard');
        await expect(page).toHaveURL(/\/login/);
    });
});
