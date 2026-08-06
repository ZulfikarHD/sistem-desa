import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-1.4 — Manajemen Profil Pengguna
 * Happy path edit profil + ganti password + edge case password lama salah /
 * NIK & role tidak dapat diubah.
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
    no_telepon?: string;
    alamat?: string;
}): void {
    const password = options.password ?? 'password';
    const phone = options.no_telepon ?? '081234567890';
    const alamat = options.alamat ?? 'Jl. Profil E2E No. 1';
    const php = [
        `\\App\\Models\\User::updateOrCreate(`,
        `['email' => ${JSON.stringify(options.email)}],`,
        `[`,
        `'name' => ${JSON.stringify(options.name)},`,
        `'nik' => ${JSON.stringify(options.nik)},`,
        `'no_telepon' => ${JSON.stringify(phone)},`,
        `'alamat' => ${JSON.stringify(alamat)},`,
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

async function loginAs(
    page: import('@playwright/test').Page,
    email: string,
    password = 'password',
): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
    await expect(page).not.toHaveURL(/\/login/);
}

async function confirmPasswordIfNeeded(
    page: import('@playwright/test').Page,
    password = 'password',
): Promise<void> {
    if (!page.url().includes('/user/confirm-password')) {
        return;
    }

    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="confirm-password-button"]').click();
    await expect(page).toHaveURL(/\/settings\/security/);
}

test.describe('US-1.4 Manajemen Profil Pengguna', () => {
    test('halaman profil menampilkan nama, telepon, alamat, email; NIK dan role readonly', async ({
        page,
    }) => {
        const stamp = Date.now();
        const email = `profil.view.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));
        const alamat = 'Jl. Merdeka Profil 12';

        ensureUser({
            email,
            name: 'Warga Profil View',
            role: 'warga',
            nik,
            no_telepon: '081211112222',
            alamat,
        });

        await loginAs(page, email);
        await page.goto('/settings/profile');

        await expect(page.getByRole('heading', { name: 'Pengaturan' })).toBeVisible();
        await expect(page.getByRole('textbox', { name: 'NIK' })).toHaveValue(nik);
        await expect(page.getByRole('textbox', { name: 'NIK' })).toHaveAttribute('readonly');
        await expect(page.getByRole('textbox', { name: 'Role' })).toHaveValue(/Warga/i);
        await expect(page.getByRole('textbox', { name: 'Role' })).toHaveAttribute('readonly');
        await expect(page.getByRole('textbox', { name: 'Nama' })).toHaveValue('Warga Profil View');
        await expect(page.getByRole('textbox', { name: 'No. Telepon' })).toHaveValue('081211112222');
        await expect(page.getByRole('textbox', { name: 'Alamat' })).toHaveValue(alamat);
        await expect(page.getByRole('textbox', { name: 'Email' })).toHaveValue(email);
    });

    test('warga dapat memperbarui data profil', async ({ page }) => {
        const stamp = Date.now();
        const email = `profil.edit.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 1);

        ensureUser({
            email,
            name: 'Warga Profil Edit',
            role: 'warga',
            nik,
        });

        await loginAs(page, email);
        await page.goto('/settings/profile');

        await page.getByRole('textbox', { name: 'Nama' }).fill('Nama Diperbarui');
        await page.getByRole('textbox', { name: 'No. Telepon' }).fill('081399998888');
        await page.getByRole('textbox', { name: 'Alamat' }).fill('Alamat Baru Desa');
        await page.locator('[data-test="update-profile-button"]').click();

        await expect(page.getByText(/Profil berhasil diperbarui/i)).toBeVisible();

        await page.reload();
        await expect(page.getByRole('textbox', { name: 'Nama' })).toHaveValue('Nama Diperbarui');
        await expect(page.getByRole('textbox', { name: 'No. Telepon' })).toHaveValue('081399998888');
        await expect(page.getByRole('textbox', { name: 'Alamat' })).toHaveValue('Alamat Baru Desa');
        await expect(page.getByRole('textbox', { name: 'NIK' })).toHaveValue(nik);
    });

    test('ganti password berhasil dengan password lama yang benar', async ({ page }) => {
        const stamp = Date.now();
        const email = `profil.pwd.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 2);

        ensureUser({
            email,
            name: 'Warga Ganti Password',
            role: 'warga',
            nik,
        });

        await loginAs(page, email);
        await page.goto('/settings/security');
        await confirmPasswordIfNeeded(page);

        await expect(page.getByText('Ganti Password').first()).toBeVisible();
        await page.getByLabel('Password Saat Ini').fill('password');
        await page.getByLabel('Password Baru', { exact: true }).fill('password-baru');
        await page.getByLabel('Konfirmasi Password Baru').fill('password-baru');
        await page.locator('[data-test="update-password-button"]').click();

        await expect(page.getByText(/Password berhasil diperbarui/i)).toBeVisible();

        await page.locator('[data-test="sidebar-menu-button"]').click();
        await page.getByRole('menuitem', { name: 'Keluar' }).first().click();
        await expect(page).toHaveURL('/');

        await loginAs(page, email, 'password-baru');
        await expect(page).toHaveURL(/\/dashboard$/);
    });

    test('ganti password gagal jika password lama salah', async ({ page }) => {
        const stamp = Date.now();
        const email = `profil.pwd.fail.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 3);

        ensureUser({
            email,
            name: 'Warga Password Fail',
            role: 'warga',
            nik,
        });

        await loginAs(page, email);
        await page.goto('/settings/security');
        await confirmPasswordIfNeeded(page);

        await page.getByLabel('Password Saat Ini').fill('password-salah');
        await page.getByLabel('Password Baru', { exact: true }).fill('password-baru');
        await page.getByLabel('Konfirmasi Password Baru').fill('password-baru');
        await page.locator('[data-test="update-password-button"]').click();

        await expect(
            page.getByText(/current password|password saat ini|tidak cocok|incorrect/i).first(),
        ).toBeVisible();
    });
});
