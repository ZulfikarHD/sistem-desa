import { test, expect } from '@playwright/test';

/**
 * Smoke E2E lintas halaman yang sudah ada — memastikan registrasi
 * tidak merusak halaman auth/home yang berbagi layout Fortify.
 */
test.describe('Smoke — halaman inti', () => {
    test('halaman beranda dapat diakses', async ({ page }) => {
        await page.goto('/');
        await expect(page).toHaveURL('/');
        await expect(page.locator('body')).toBeVisible();
    });

    test('halaman login dapat diakses', async ({ page }) => {
        await page.goto('/login');
        await expect(page.locator('input[name="email"]')).toBeVisible();
        await expect(page.locator('input[name="password"]')).toBeVisible();
        await expect(page.locator('[data-test="login-button"]')).toBeVisible();
        await expect(page.getByText('Masuk ke akun Anda')).toBeVisible();
    });
});
