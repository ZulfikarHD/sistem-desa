import { test, expect } from '@playwright/test';

/**
 * Smoke E2E lintas halaman inti setelah redesign beranda + auth brand.
 */
test.describe('Smoke — halaman inti', () => {
    test('halaman beranda menampilkan brand dan CTA auth', async ({ page }) => {
        await page.goto('/');

        await expect(page).toHaveURL('/');
        await expect(page.getByText('Pelayanan Surat Desa').first()).toBeVisible();
        await expect(
            page.getByText('Ajukan surat keterangan desa secara daring, tanpa antre di kantor.'),
        ).toBeVisible();
        await expect(page.locator('[data-test="welcome-login"]')).toBeVisible();
        await expect(page.locator('[data-test="welcome-register"]')).toBeVisible();
    });

    test('halaman login dapat diakses dengan layout brand', async ({ page }) => {
        await page.goto('/login');

        await expect(page.locator('input[name="email"]')).toBeVisible();
        await expect(page.locator('input[name="password"]')).toBeVisible();
        await expect(page.locator('[data-test="login-button"]')).toBeVisible();
        await expect(page.getByText('Masuk ke akun Anda')).toBeVisible();
        await expect(page.getByText('Pelayanan Surat Desa').first()).toBeVisible();
    });

    test('halaman registrasi dapat diakses dengan layout brand', async ({ page }) => {
        await page.goto('/register');

        await expect(page.getByText('Registrasi Akun Warga')).toBeVisible();
        await expect(page.locator('[data-test="register-user-button"]')).toBeVisible();
        await expect(page.getByText('Pelayanan Surat Desa').first()).toBeVisible();
    });
});
