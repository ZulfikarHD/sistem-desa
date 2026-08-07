import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-3.1 — Form Pengajuan Surat Keterangan (Warga)
 * US-3.2 / US-9.3 — Unggah mengikuti persyaratan terstruktur
 * US-3.3 — Validasi kelengkapan (wajib vs opsional)
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const fixturesDir = path.join(projectRoot, 'e2e', 'fixtures');

type PersyaratanRowInput = {
    nama: string;
    cara_pemenuhan: 'unggah' | 'bawa_kantor' | 'info';
    is_wajib: boolean;
};

function uniqueNik(suffix: number): string {
    return `3205050505${String(suffix).padStart(6, '0')}`;
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
        `'alamat' => 'Jl. E2E Pengajuan Surat No. 1',`,
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

function defaultKtpKkRows(): PersyaratanRowInput[] {
    return [
        { nama: 'Fotokopi KTP', cara_pemenuhan: 'unggah', is_wajib: true },
        { nama: 'Fotokopi KK', cara_pemenuhan: 'unggah', is_wajib: true },
    ];
}

function rowsToPhpArray(rows: PersyaratanRowInput[]): string {
    return (
        '[' +
        rows
            .map(
                (row, index) =>
                    `[` +
                    `'nama' => ${JSON.stringify(row.nama)}, ` +
                    `'cara_pemenuhan' => ${JSON.stringify(row.cara_pemenuhan)}, ` +
                    `'is_wajib' => ${row.is_wajib ? 'true' : 'false'}, ` +
                    `'urutan' => ${index}` +
                    `]`,
            )
            .join(', ') +
        ']'
    );
}

function ensureJenisSurat(namaSurat: string, rows: PersyaratanRowInput[] = defaultKtpKkRows()): void {
    // Hindari variabel $ di string --execute (bash meng-expand meskipun di JSON.stringify).
    const php = [
        `\\App\\Models\\JenisSurat::query()->updateOrCreate(`,
        `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
        `['deskripsi' => 'Deskripsi e2e pengajuan', 'persyaratan_dokumen' => 'placeholder']`,
        `)->syncPersyaratan(${rowsToPhpArray(rows)});`,
    ].join('');

    execSync(`php artisan tinker --execute ${JSON.stringify(php)}`, {
        cwd: projectRoot,
        stdio: 'pipe',
    });
}

function persyaratanIdsByNama(namaSurat: string): Record<string, number> {
    const php = [
        `echo json_encode(`,
        `\\App\\Models\\JenisSurat::where('nama_surat', ${JSON.stringify(namaSurat)})`,
        `->firstOrFail()`,
        `->persyaratan()`,
        `->pluck('id', 'nama')`,
        `);`,
    ].join('');

    const output = execSync(`php artisan tinker --execute ${JSON.stringify(php)}`, {
        cwd: projectRoot,
        encoding: 'utf8',
    });

    const match = output.match(/\{[\s\S]*\}/);
    if (!match) {
        throw new Error(`Gagal membaca persyaratan IDs untuk ${namaSurat}: ${output}`);
    }

    return JSON.parse(match[0]) as Record<string, number>;
}

async function uploadDokumenBySyaratId(
    page: import('@playwright/test').Page,
    syaratId: number,
    filePath: string,
): Promise<void> {
    await page.locator(`[data-test="pengajuan-surat-dokumen-input-${syaratId}"]`).setInputFiles(filePath);
    await expect(page.locator(`[data-test="pengajuan-surat-dokumen-preview-${syaratId}"]`)).toBeVisible({
        timeout: 30_000,
    });
}

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

async function selectJenisSuratByName(page: import('@playwright/test').Page, namaSurat: string): Promise<void> {
    await page.getByRole('combobox', { name: 'Jenis Surat' }).selectOption({ label: namaSurat });
}

test.describe('US-3.1 Form Pengajuan Surat Keterangan', () => {
    test('guest yang mengakses halaman pengajuan diarahkan ke login', async ({ page }) => {
        await page.goto('/pengajuan-surat');
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('input[name="email"]')).toBeVisible();
    });

    test('admin yang mengakses halaman pengajuan mendapat 403', async ({ page }) => {
        const stamp = Date.now();
        const email = `admin.pengajuan.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));

        ensureUser({
            email,
            name: 'Admin Pengajuan Forbid',
            role: 'admin',
            nik,
        });

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        const response = await page.goto('/pengajuan-surat');
        expect(response?.status()).toBe(403);
        await expect(page.locator('[data-test="pengajuan-surat-heading"]')).toHaveCount(0);
    });

    test('warga dapat mengajukan surat dan mendapat nomor pengajuan otomatis', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.pengajuan.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 1);
        const namaSurat = `Surat Pengajuan E2E ${stamp}`;
        const keperluan = `Keperluan administrasi bank ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Pengajuan Happy',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat);
        const ids = persyaratanIdsByNama(namaSurat);

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/dashboard$/);

        await page.goto('/pengajuan-surat');
        await expect(page.locator('[data-test="pengajuan-surat-heading"]')).toBeVisible();

        await selectJenisSuratByName(page, namaSurat);
        await expect(page.locator('[data-test="pengajuan-surat-dokumen-section"]')).toBeVisible();

        await uploadDokumenBySyaratId(page, ids['Fotokopi KTP'], path.join(fixturesDir, 'ktp-sample.jpg'));
        await uploadDokumenBySyaratId(page, ids['Fotokopi KK'], path.join(fixturesDir, 'kk-sample.png'));

        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill(keperluan);
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toBeVisible();
        const nomor = page.locator('[data-test="pengajuan-surat-nomor"]');
        await expect(nomor).toBeVisible();
        await expect(nomor).toHaveText(/^PJ-\d{8}-\d+$/);
    });

    test('validasi gagal jika jenis surat tidak dipilih', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.pengajuan.required.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 2);

        ensureUser({
            email,
            name: 'Warga Pengajuan Required',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(`Surat Validasi Jenis ${stamp}`);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill('Keperluan tanpa jenis surat');
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.getByText(/Jenis surat wajib dipilih/i)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });

    test('validasi gagal jika keperluan kosong', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.pengajuan.keperluan.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 3);
        const namaSurat = `Surat Validasi Keperluan ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Pengajuan Keperluan',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await selectJenisSuratByName(page, namaSurat);
        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill('');
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.getByText(/Keperluan wajib diisi/i)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });
});

test.describe('US-3.2 / US-9.3 Unggah Dokumen Persyaratan Terstruktur', () => {
    test('area unggah muncul sesuai baris syarat unggah jenis surat terpilih', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.dokumen.section.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 10);
        const namaSurat = `Surat Dokumen Section ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Dokumen Section',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, defaultKtpKkRows());
        const ids = persyaratanIdsByNama(namaSurat);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await expect(page.locator('[data-test="pengajuan-surat-dokumen-section"]')).toHaveCount(0);

        await selectJenisSuratByName(page, namaSurat);

        await expect(page.locator('[data-test="pengajuan-surat-dokumen-section"]')).toBeVisible();
        await expect(page.locator(`[data-test="pengajuan-surat-dokumen-input-${ids['Fotokopi KTP']}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="pengajuan-surat-dokumen-input-${ids['Fotokopi KK']}"]`)).toBeVisible();
        await expect(page.getByText('Wajib diunggah').first()).toBeVisible();
    });

    test('warga dapat melihat preview dokumen sebelum submit final', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.dokumen.preview.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 11);
        const namaSurat = `Surat Dokumen Preview ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Dokumen Preview',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, [{ nama: 'Fotokopi KTP', cara_pemenuhan: 'unggah', is_wajib: true }]);
        const ids = persyaratanIdsByNama(namaSurat);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await selectJenisSuratByName(page, namaSurat);

        await uploadDokumenBySyaratId(page, ids['Fotokopi KTP'], path.join(fixturesDir, 'ktp-sample.jpg'));
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });

    test('validasi gagal jika format file tidak didukung', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.dokumen.invalid.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 12);
        const namaSurat = `Surat Dokumen Invalid ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Dokumen Invalid',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, [{ nama: 'Fotokopi KTP', cara_pemenuhan: 'unggah', is_wajib: true }]);
        const ids = persyaratanIdsByNama(namaSurat);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await selectJenisSuratByName(page, namaSurat);
        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill('Keperluan dengan file invalid');

        await page
            .locator(`[data-test="pengajuan-surat-dokumen-input-${ids['Fotokopi KTP']}"]`)
            .setInputFiles(path.join(fixturesDir, 'invalid-sample.txt'));

        await page.waitForTimeout(2000);

        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.getByText(/Format Fotokopi KTP harus JPG, PNG, atau PDF/i)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });
});

test.describe('US-3.3 Validasi Kelengkapan Pengajuan', () => {
    test('validasi gagal jika dokumen KTP wajib belum diunggah', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.kelengkapan.ktp.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 20);
        const namaSurat = `Surat Kelengkapan KTP ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Kelengkapan KTP',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, [{ nama: 'Fotokopi KTP', cara_pemenuhan: 'unggah', is_wajib: true }]);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await selectJenisSuratByName(page, namaSurat);
        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill('Keperluan tanpa unggah KTP');
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.getByText(/Dokumen Fotokopi KTP wajib diunggah/i)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });

    test('validasi gagal jika KK wajib belum diunggah meskipun KTP sudah diunggah', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.kelengkapan.kk.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 21);
        const namaSurat = `Surat Kelengkapan KK ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Kelengkapan KK',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, defaultKtpKkRows());
        const ids = persyaratanIdsByNama(namaSurat);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await selectJenisSuratByName(page, namaSurat);
        await uploadDokumenBySyaratId(page, ids['Fotokopi KTP'], path.join(fixturesDir, 'ktp-sample.jpg'));
        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill('Keperluan hanya KTP, KK belum');
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.getByText(/Dokumen Fotokopi KK wajib diunggah/i)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });

    test('pengajuan tersimpan dengan status diajukan hanya jika semua dokumen wajib lengkap', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.kelengkapan.lengkap.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 22);
        const namaSurat = `Surat Kelengkapan Lengkap ${stamp}`;

        ensureUser({
            email,
            name: 'Warga Kelengkapan Lengkap',
            role: 'warga',
            nik,
        });
        ensureJenisSurat(namaSurat, defaultKtpKkRows());
        const ids = persyaratanIdsByNama(namaSurat);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');

        await selectJenisSuratByName(page, namaSurat);
        await uploadDokumenBySyaratId(page, ids['Fotokopi KTP'], path.join(fixturesDir, 'ktp-sample.jpg'));
        await uploadDokumenBySyaratId(page, ids['Fotokopi KK'], path.join(fixturesDir, 'kk-sample.png'));
        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill(`Keperluan lengkap ${stamp}`);
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-nomor"]')).toHaveText(/^PJ-\d{8}-\d+$/);
    });
});
