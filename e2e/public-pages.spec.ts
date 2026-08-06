import { test, expect } from '@playwright/test';

/**
 * Navigasi beranda → login/register setelah redesign public pages.
 */
test.describe('Public pages — beranda & auth', () => {
    test('CTA Masuk dari beranda membuka halaman login', async ({ page }) => {
        await page.goto('/');

        await page.locator('[data-test="welcome-login"]').click();

        await expect(page).toHaveURL(/\/login/);
        await expect(page.getByText('Masuk ke akun Anda')).toBeVisible();
        await expect(page.locator('[data-test="login-button"]')).toBeVisible();
    });

    test('CTA Daftar dari beranda membuka halaman registrasi', async ({ page }) => {
        await page.goto('/');

        await page.locator('[data-test="welcome-register"]').click();

        await expect(page).toHaveURL(/\/register/);
        await expect(page.getByText('Registrasi Akun Warga')).toBeVisible();
        await expect(page.locator('[data-test="register-user-button"]')).toBeVisible();
    });

    test('tautan Daftar di login menuju registrasi', async ({ page }) => {
        await page.goto('/login');

        await page.getByRole('link', { name: 'Daftar' }).click();

        await expect(page).toHaveURL(/\/register/);
        await expect(page.getByText('Registrasi Akun Warga')).toBeVisible();
    });

    test('tautan Masuk di registrasi menuju login', async ({ page }) => {
        await page.goto('/register');

        await page.getByRole('link', { name: 'Masuk' }).click();

        await expect(page).toHaveURL(/\/login/);
        await expect(page.getByText('Masuk ke akun Anda')).toBeVisible();
    });
});
