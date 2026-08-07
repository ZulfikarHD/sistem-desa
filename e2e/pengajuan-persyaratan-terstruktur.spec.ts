import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-9.3 — Form pengajuan mengikuti aturan persyaratan terstruktur.
 * Happy path: campuran unggah wajib + opsional + bawa kantor.
 * Edge: opsional tidak memblokir; bawa kantor tanpa file input; wajib memblokir.
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
        `'alamat' => 'Jl. E2E US-9.3 No. 1',`,
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

function ensureJenisSurat(namaSurat: string, rows: PersyaratanRowInput[]): void {
    const php = [
        `\\App\\Models\\JenisSurat::query()->updateOrCreate(`,
        `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
        `['deskripsi' => 'Deskripsi e2e US-9.3', 'persyaratan_dokumen' => 'placeholder']`,
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

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

async function selectJenisSuratByName(page: import('@playwright/test').Page, namaSurat: string): Promise<void> {
    await page.getByRole('combobox', { name: 'Jenis Surat' }).selectOption({ label: namaSurat });
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

const mixedRows: PersyaratanRowInput[] = [
    { nama: 'Fotokopi KTP', cara_pemenuhan: 'unggah', is_wajib: true },
    { nama: 'NPWP (jika ada)', cara_pemenuhan: 'unggah', is_wajib: false },
    { nama: 'Surat pengantar RT/RW', cara_pemenuhan: 'bawa_kantor', is_wajib: true },
    { nama: 'Datang pada jam kerja', cara_pemenuhan: 'info', is_wajib: true },
];

test.describe('US-9.3 Form Pengajuan Mengikuti Aturan Terstruktur', () => {
    test('warga melihat badge yang sama dengan pratinjau admin dan slot unggah hanya untuk cara unggah', async ({
        page,
    }) => {
        const stamp = Date.now();
        const email = `warga.us93.badge.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));
        const namaSurat = `Surat US93 Badge ${stamp}`;

        ensureUser({ email, name: 'Warga US93 Badge', role: 'warga', nik });
        ensureJenisSurat(namaSurat, mixedRows);
        const ids = persyaratanIdsByNama(namaSurat);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');
        await selectJenisSuratByName(page, namaSurat);

        await expect(page.locator('[data-test="pengajuan-surat-persyaratan-list"]')).toBeVisible();
        await expect(page.locator(`[data-test="pengajuan-surat-persyaratan-badge-${ids['Fotokopi KTP']}"]`)).toHaveText(
            'Wajib diunggah',
        );
        await expect(page.locator(`[data-test="pengajuan-surat-persyaratan-badge-${ids['NPWP (jika ada)']}"]`)).toHaveText(
            'Boleh dikosongkan',
        );
        await expect(
            page.locator(`[data-test="pengajuan-surat-persyaratan-badge-${ids['Surat pengantar RT/RW']}"]`),
        ).toHaveText('Bawa ke kantor');
        await expect(
            page.locator(`[data-test="pengajuan-surat-persyaratan-badge-${ids['Datang pada jam kerja']}"]`),
        ).toHaveText('Informasi');

        await expect(page.locator(`[data-test="pengajuan-surat-dokumen-input-${ids['Fotokopi KTP']}"]`)).toBeVisible();
        await expect(
            page.locator(`[data-test="pengajuan-surat-dokumen-input-${ids['NPWP (jika ada)']}"]`),
        ).toBeVisible();
        await expect(
            page.locator(`[data-test="pengajuan-surat-dokumen-input-${ids['Surat pengantar RT/RW']}"]`),
        ).toHaveCount(0);
        await expect(
            page.locator(`[data-test="pengajuan-surat-dokumen-input-${ids['Datang pada jam kerja']}"]`),
        ).toHaveCount(0);

        await expect(
            page.getByText('Siapkan berkas ini dan bawa saat diminta petugas / saat pengambilan.'),
        ).toBeVisible();
    });

    test('submit berhasil dengan hanya unggah wajib; opsional dan bawa kantor tidak memblokir', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.us93.opsional.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 1);
        const namaSurat = `Surat US93 Opsional ${stamp}`;

        ensureUser({ email, name: 'Warga US93 Opsional', role: 'warga', nik });
        ensureJenisSurat(namaSurat, mixedRows);
        const ids = persyaratanIdsByNama(namaSurat);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');
        await selectJenisSuratByName(page, namaSurat);

        await uploadDokumenBySyaratId(page, ids['Fotokopi KTP'], path.join(fixturesDir, 'ktp-sample.jpg'));
        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill(`Keperluan opsional kosong ${stamp}`);
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-nomor"]')).toHaveText(/^PJ-\d{8}-\d+$/);
    });

    test('submit ditolak jika unggah wajib kosong meskipun opsional sudah diisi', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.us93.wajib.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 2);
        const namaSurat = `Surat US93 Wajib ${stamp}`;

        ensureUser({ email, name: 'Warga US93 Wajib', role: 'warga', nik });
        ensureJenisSurat(namaSurat, mixedRows);
        const ids = persyaratanIdsByNama(namaSurat);

        await loginAs(page, email);
        await page.goto('/pengajuan-surat');
        await selectJenisSuratByName(page, namaSurat);

        await uploadDokumenBySyaratId(page, ids['NPWP (jika ada)'], path.join(fixturesDir, 'kk-sample.png'));
        await page.locator('[data-test="pengajuan-surat-keperluan-input"]').fill('Hanya opsional');
        await page.locator('[data-test="pengajuan-surat-submit-button"]').click();

        await expect(page.getByText(/Dokumen Fotokopi KTP wajib diunggah/i)).toBeVisible();
        await expect(page.locator('[data-test="pengajuan-surat-success"]')).toHaveCount(0);
    });
});
