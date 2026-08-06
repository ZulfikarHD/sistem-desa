import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-1.3 — Middleware Proteksi Role
 * Happy path akses dashboard sesuai role + edge case akses silang (403)
 * + guest diarahkan ke login.
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

function uniqueNik(suffix: number): string {
    return `3203030303${String(suffix).padStart(6, '0')}`;
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
        `'alamat' => 'Jl. E2E Role Middleware No. 1',`,
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

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

test.describe('US-1.3 Middleware Proteksi Role', () => {
    test('guest yang mengakses dashboard warga diarahkan ke login', async ({ page }) => {
        await page.goto('/dashboard');
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('input[name="email"]')).toBeVisible();
    });

    test('guest yang mengakses dashboard admin diarahkan ke login', async ({ page }) => {
        await page.goto('/admin/dashboard');
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('input[name="email"]')).toBeVisible();
    });

    test('warga dapat mengakses dashboard warga', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.role.ok.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));

        ensureUser({
            email,
            name: 'Warga Role OK',
            role: 'warga',
            nik,
        });

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/dashboard$/);
        await expect(page.locator('[data-test="dashboard-warga-heading"]')).toBeVisible();
    });

    test('admin dapat mengakses dashboard admin', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.role.ok.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 1);

        ensureUser({
            email,
            name: 'Admin Role OK',
            role: 'admin',
            nik,
        });

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await expect(page.locator('[data-test="dashboard-admin-heading"]')).toBeVisible();
    });

    test('warga yang mengakses dashboard admin mendapat 403', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.role.forbid.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 2);

        ensureUser({
            email,
            name: 'Warga Role Forbid',
            role: 'warga',
            nik,
        });

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/dashboard$/);

        const response = await page.goto('/admin/dashboard');
        expect(response?.status()).toBe(403);
        await expect(page.locator('[data-test="dashboard-admin-heading"]')).toHaveCount(0);
        await expect(page.getByText(/403|Forbidden|tidak diizinkan|Unauthorized/i).first()).toBeVisible();
    });

    test('admin yang mengakses dashboard warga mendapat 403', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.role.forbid.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 3);

        ensureUser({
            email,
            name: 'Admin Role Forbid',
            role: 'admin',
            nik,
        });

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        const response = await page.goto('/dashboard');
        expect(response?.status()).toBe(403);
        await expect(page.locator('[data-test="dashboard-warga-heading"]')).toHaveCount(0);
        await expect(page.getByText(/403|Forbidden|tidak diizinkan|Unauthorized/i).first()).toBeVisible();
    });

    test('warga dan admin tetap dapat mengakses halaman profil bersama', async ({ page }) => {
        const stamp = Date.now();
        const wargaEmail = `warga.profile.${stamp}@example.com`;
        const adminEmail = `admin.profile.${stamp}@example.com`;
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 4);
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 5);

        ensureUser({
            email: wargaEmail,
            name: 'Warga Profile Shared',
            role: 'warga',
            nik: wargaNik,
        });
        ensureUser({
            email: adminEmail,
            name: 'Admin Profile Shared',
            role: 'admin',
            nik: adminNik,
        });

        await loginAs(page, wargaEmail);
        await page.goto('/settings/profile');
        await expect(page).toHaveURL(/\/settings\/profile/);
        await expect(page.locator('body')).toBeVisible();

        await page.locator('[data-test="sidebar-menu-button"]').click();
        await page.getByRole('menuitem', { name: 'Keluar' }).first().click();
        await expect(page).toHaveURL('/');

        await loginAs(page, adminEmail);
        await page.goto('/settings/profile');
        await expect(page).toHaveURL(/\/settings\/profile/);
        await expect(page.locator('body')).toBeVisible();
    });
});
