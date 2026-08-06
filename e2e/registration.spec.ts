import { test, expect } from '@playwright/test';

/**
 * US-1.1 — Registrasi Akun Warga
 * Mencakup happy path + skenario kegagalan (NIK tidak valid / duplikat).
 */
function uniqueNik(suffix: number): string {
    return `3201010101${String(suffix).padStart(6, '0')}`;
}

test.describe('US-1.1 Registrasi Akun Warga', () => {
    test('halaman registrasi menampilkan semua field wajib', async ({ page }) => {
        await page.goto('/register');

        await expect(page.getByText('Registrasi Akun Warga')).toBeVisible();
        await expect(page.getByRole('textbox', { name: 'NIK' })).toBeVisible();
        await expect(page.getByRole('textbox', { name: 'Nama' })).toBeVisible();
        await expect(page.getByRole('textbox', { name: 'No. Telepon' })).toBeVisible();
        await expect(page.getByRole('textbox', { name: 'Alamat' })).toBeVisible();
        await expect(page.getByRole('textbox', { name: 'Email' })).toBeVisible();
        await expect(page.locator('input[name="password"]')).toBeVisible();
        await expect(page.locator('input[name="password_confirmation"]')).toBeVisible();
        await expect(page.locator('[data-test="register-user-button"]')).toBeVisible();
    });

    test('warga dapat registrasi lalu diarahkan ke halaman login', async ({ page }) => {
        const stamp = Date.now();
        const nik = uniqueNik(Number(String(stamp).slice(-6)));
        const email = `warga.${stamp}@example.com`;

        await page.goto('/register');

        await page.locator('input[name="nik"]').fill(nik);
        await page.locator('input[name="name"]').fill('Budi Santoso');
        await page.locator('input[name="no_telepon"]').fill('081234567890');
        await page.locator('textarea[name="alamat"]').fill('Jl. Merdeka No. 1, Desa Contoh');
        await page.locator('input[name="email"]').fill(email);
        await page.locator('input[name="password"]').fill('password');
        await page.locator('input[name="password_confirmation"]').fill('password');

        await page.locator('[data-test="register-user-button"]').click();

        await expect(page).toHaveURL(/\/login/);
        await expect(page.getByText(/Registrasi berhasil/i)).toBeVisible();
    });

    test('registrasi gagal jika NIK bukan 16 digit', async ({ page }) => {
        await page.goto('/register');

        await page.locator('input[name="nik"]').fill('12345');
        await page.locator('input[name="name"]').fill('Budi Santoso');
        await page.locator('input[name="no_telepon"]').fill('081234567890');
        await page.locator('textarea[name="alamat"]').fill('Jl. Merdeka No. 1');
        await page.locator('input[name="email"]').fill(`invalid.nik.${Date.now()}@example.com`);
        await page.locator('input[name="password"]').fill('password');
        await page.locator('input[name="password_confirmation"]').fill('password');

        await page.locator('[data-test="register-user-button"]').click();

        await expect(page).toHaveURL(/\/register/);
        await expect(page.getByText(/NIK|16 digit/i).first()).toBeVisible();
    });

    test('registrasi gagal jika NIK sudah terdaftar', async ({ page }) => {
        const stamp = Date.now();
        const nik = uniqueNik(Number(String(stamp).slice(-6)));
        const email1 = `dup.a.${stamp}@example.com`;
        const email2 = `dup.b.${stamp}@example.com`;

        // Registrasi pertama sukses
        await page.goto('/register');
        await page.locator('input[name="nik"]').fill(nik);
        await page.locator('input[name="name"]').fill('Warga Pertama');
        await page.locator('input[name="no_telepon"]').fill('081111111111');
        await page.locator('textarea[name="alamat"]').fill('Alamat Pertama');
        await page.locator('input[name="email"]').fill(email1);
        await page.locator('input[name="password"]').fill('password');
        await page.locator('input[name="password_confirmation"]').fill('password');
        await page.locator('[data-test="register-user-button"]').click();
        await expect(page).toHaveURL(/\/login/);

        // Registrasi kedua dengan NIK sama harus gagal
        await page.goto('/register');
        await page.locator('input[name="nik"]').fill(nik);
        await page.locator('input[name="name"]').fill('Warga Kedua');
        await page.locator('input[name="no_telepon"]').fill('082222222222');
        await page.locator('textarea[name="alamat"]').fill('Alamat Kedua');
        await page.locator('input[name="email"]').fill(email2);
        await page.locator('input[name="password"]').fill('password');
        await page.locator('input[name="password_confirmation"]').fill('password');
        await page.locator('[data-test="register-user-button"]').click();

        await expect(page).toHaveURL(/\/register/);
        await expect(page.getByText(/NIK sudah terdaftar|already been taken|unique/i).first()).toBeVisible();
    });
});
