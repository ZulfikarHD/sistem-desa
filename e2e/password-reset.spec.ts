import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-1.5 — Lupa Password (Reset Password)
 * Happy path request + reset + login password baru;
 * edge case token tidak valid.
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

function uniqueNik(suffix: number): string {
    return `3205050505${String(suffix).padStart(6, '0')}`;
}

function runTinker(php: string): string {
    return execSync(`php artisan tinker --execute ${JSON.stringify(php)}`, {
        cwd: projectRoot,
        encoding: 'utf8',
        stdio: ['pipe', 'pipe', 'pipe'],
    });
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
        `'alamat' => 'Jl. Reset Password E2E No. 1',`,
        `'role' => ${JSON.stringify(options.role)},`,
        `'password' => ${JSON.stringify(password)},`,
        `'email_verified_at' => now(),`,
        `]`,
        `);`,
    ].join('');

    runTinker(php);
}

function createResetToken(email: string): string {
    // Hindari variabel `$...` di --execute: shell dapat mengekspansi `$user` menjadi kosong.
    const php = `echo \\Illuminate\\Support\\Facades\\Password::broker()->createToken(\\App\\Models\\User::where('email', ${JSON.stringify(email)})->firstOrFail());`;

    const output = runTinker(php).trim();
    const token = output
        .split('\n')
        .map((line) => line.trim())
        .filter((line) => line.length > 0 && !line.startsWith('>') && !line.includes('ERROR'))
        .at(-1);

    if (!token || token.length < 20) {
        throw new Error(`Gagal membuat reset token. Output tinker: ${output}`);
    }

    return token;
}

test.describe('US-1.5 Lupa Password (Reset Password)', () => {
    test('halaman login menampilkan tautan Lupa Password', async ({ page }) => {
        await page.goto('/login');
        await expect(page.getByRole('link', { name: 'Lupa Password?' })).toBeVisible();
    });

    test('form lupa password menerima email dan menampilkan status', async ({ page }) => {
        const stamp = Date.now();
        const email = `reset.request.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));

        ensureUser({
            email,
            name: 'Warga Reset Request',
            role: 'warga',
            nik,
        });

        await page.goto('/login');
        await page.getByRole('link', { name: 'Lupa Password?' }).click();
        await expect(page).toHaveURL(/\/forgot-password/);
        await expect(page.getByText('Lupa Password').first()).toBeVisible();

        await page.locator('input[name="email"]').fill(email);
        await page.locator('[data-test="email-password-reset-link-button"]').click();

        await expect(
            page.getByText(/reset link|tautan|password reset|email/i).first(),
        ).toBeVisible();
    });

    test('pengguna dapat reset password dengan token lalu login password baru', async ({
        page,
    }) => {
        const stamp = Date.now();
        const email = `reset.ok.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 1);
        const newPassword = 'password-reset-ok';

        ensureUser({
            email,
            name: 'Warga Reset OK',
            role: 'warga',
            nik,
        });

        const token = createResetToken(email);

        await page.goto(`/reset-password/${token}?email=${encodeURIComponent(email)}`);
        await expect(page.getByText('Reset Password').first()).toBeVisible();

        await page.locator('input[name="email"]').fill(email);
        await page.locator('input[name="password"]').fill(newPassword);
        await page.locator('input[name="password_confirmation"]').fill(newPassword);
        await page.locator('[data-test="reset-password-button"]').click();

        await expect(page).toHaveURL(/\/login/);

        await page.locator('input[name="email"]').fill(email);
        await page.locator('input[name="password"]').fill(newPassword);
        await page.locator('[data-test="login-button"]').click();

        await expect(page).toHaveURL(/\/dashboard$/);
        await expect(page.locator('[data-test="dashboard-warga-heading"]')).toBeVisible();
    });

    test('reset gagal dengan token tidak valid', async ({ page }) => {
        const stamp = Date.now();
        const email = `reset.bad.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 2);

        ensureUser({
            email,
            name: 'Warga Reset Bad Token',
            role: 'warga',
            nik,
        });

        await page.goto(
            `/reset-password/token-tidak-valid-sama-sekali?email=${encodeURIComponent(email)}`,
        );
        await page.locator('input[name="password"]').fill('password-baru');
        await page.locator('input[name="password_confirmation"]').fill('password-baru');
        await page.locator('[data-test="reset-password-button"]').click();

        await expect(page).not.toHaveURL(/\/login$/);
        await expect(
            page.getByText(/token|invalid|kadaluarsa|expired|tidak valid/i).first(),
        ).toBeVisible();

        await page.goto('/login');
        await page.locator('input[name="email"]').fill(email);
        await page.locator('input[name="password"]').fill('password');
        await page.locator('[data-test="login-button"]').click();
        await expect(page).toHaveURL(/\/dashboard$/);
    });
});
