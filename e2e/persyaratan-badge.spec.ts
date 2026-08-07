import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-9.4 — Tampilan Persyaratan Publik & Warga dengan Badge Jelas
 * Happy: list + detail badges for all cara_pemenuhan.
 * Edge: soft-deleted hidden; guest access; search by syarat nama; empty search.
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

type PersyaratanRowInput = {
    nama: string;
    cara_pemenuhan: 'unggah' | 'bawa_kantor' | 'info';
    is_wajib: boolean;
};

function uniqueNik(suffix: number): string {
    return `3207070707${String(suffix).padStart(6, '0')}`;
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
        `'alamat' => 'Jl. E2E US-9.4 No. 1',`,
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

function ensureJenisSurat(namaSurat: string, rows: PersyaratanRowInput[], deskripsi = 'Deskripsi e2e US-9.4'): void {
    const php = [
        `\\App\\Models\\JenisSurat::query()->updateOrCreate(`,
        `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
        `['deskripsi' => ${JSON.stringify(deskripsi)}, 'persyaratan_dokumen' => 'placeholder']`,
        `)->syncPersyaratan(${rowsToPhpArray(rows)});`,
    ].join('');

    execSync(`php artisan tinker --execute ${JSON.stringify(php)}`, {
        cwd: projectRoot,
        stdio: 'pipe',
    });
}

function softDeleteJenisSurat(namaSurat: string): void {
    const php = [
        `\\App\\Models\\JenisSurat::where('nama_surat', ${JSON.stringify(namaSurat)})`,
        `->get()->each->delete();`,
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

const mixedRows: PersyaratanRowInput[] = [
    { nama: 'Fotokopi KTP', cara_pemenuhan: 'unggah', is_wajib: true },
    { nama: 'NPWP (jika ada)', cara_pemenuhan: 'unggah', is_wajib: false },
    { nama: 'Surat pengantar RT/RW', cara_pemenuhan: 'bawa_kantor', is_wajib: true },
    { nama: 'Datang pada jam kerja', cara_pemenuhan: 'info', is_wajib: true },
];

test.describe('US-9.4 Persyaratan Publik/Warga dengan Badge', () => {
    test('warga melihat daftar item ber-badge di list dan detail', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.us94.badge.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));
        const namaSurat = `Surat US94 Badge ${stamp}`;

        ensureUser({ email, name: 'Warga US94 Badge', role: 'warga', nik });
        ensureJenisSurat(namaSurat, mixedRows);
        const ids = persyaratanIdsByNama(namaSurat);

        await loginAs(page, email);
        await page.goto('/persyaratan-dokumen');
        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(namaSurat);
        await expect(page.getByText(namaSurat)).toBeVisible();

        await expect(page.locator(`[data-test="persyaratan-dokumen-preview-badge-${ids['Fotokopi KTP']}"]`)).toContainText(
            'Wajib diunggah',
        );
        await expect(
            page.locator(`[data-test="persyaratan-dokumen-preview-badge-${ids['NPWP (jika ada)']}"]`),
        ).toContainText('Boleh dikosongkan');
        await expect(
            page.locator(`[data-test="persyaratan-dokumen-preview-badge-${ids['Surat pengantar RT/RW']}"]`),
        ).toContainText('Bawa ke kantor');
        await expect(
            page.locator(`[data-test="persyaratan-dokumen-preview-badge-${ids['Datang pada jam kerja']}"]`),
        ).toContainText('Informasi');

        await page.getByRole('button', { name: 'Lihat Detail' }).first().click();
        await expect(page.locator('[data-test="persyaratan-dokumen-detail-title"]')).toContainText(namaSurat);
        await expect(page.locator(`[data-test="persyaratan-dokumen-detail-badge-${ids['Fotokopi KTP']}"]`)).toContainText(
            'Wajib diunggah',
        );
        await expect(
            page.locator(`[data-test="persyaratan-dokumen-detail-item-${ids['Surat pengantar RT/RW']}"]`),
        ).toBeVisible();
        await expect(page.locator('[data-test="persyaratan-dokumen-detail-persyaratan"]')).toBeVisible();
        await expect(page.getByText(/blok teks mentah/i)).toHaveCount(0);
    });

    test('guest dapat melihat badge tanpa login', async ({ page }) => {
        const stamp = Date.now();
        const namaSurat = `Surat US94 Guest ${stamp}`;

        ensureJenisSurat(namaSurat, mixedRows);
        const ids = persyaratanIdsByNama(namaSurat);

        const response = await page.goto('/persyaratan-dokumen');
        expect(response?.status()).toBe(200);
        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(namaSurat);
        await expect(page.getByText(namaSurat)).toBeVisible();
        await expect(page.locator('[data-test="persyaratan-dokumen-guest-cta"]')).toBeVisible();
        await expect(page.locator(`[data-test="persyaratan-dokumen-preview-badge-${ids['Fotokopi KTP']}"]`)).toContainText(
            'Wajib diunggah',
        );
    });

    test('jenis surat terarsip tetap disembunyikan', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.us94.arsip.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 1);
        const activeName = `Surat US94 Aktif ${stamp}`;
        const archivedName = `Surat US94 Arsip ${stamp}`;

        ensureUser({ email, name: 'Warga US94 Arsip', role: 'warga', nik });
        ensureJenisSurat(activeName, [{ nama: 'Fotokopi KTP', cara_pemenuhan: 'unggah', is_wajib: true }]);
        ensureJenisSurat(archivedName, [{ nama: 'Fotokopi KTP', cara_pemenuhan: 'unggah', is_wajib: true }]);
        softDeleteJenisSurat(archivedName);

        await loginAs(page, email);
        await page.goto('/persyaratan-dokumen');
        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(`US94 ${stamp}`);
        await expect(page.getByText(activeName)).toBeVisible();
        await expect(page.getByText(archivedName)).toHaveCount(0);
    });

    test('pencarian berdasarkan nama syarat terstruktur berfungsi', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.us94.search.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 2);
        const targetName = `Surat US94 Cari Target ${stamp}`;
        const otherName = `Surat US94 Cari Lain ${stamp}`;
        const uniqueSyarat = `Slip Gaji UnikUS94${stamp}`;

        ensureUser({ email, name: 'Warga US94 Search', role: 'warga', nik });
        ensureJenisSurat(targetName, [{ nama: uniqueSyarat, cara_pemenuhan: 'unggah', is_wajib: false }]);
        ensureJenisSurat(otherName, [{ nama: 'Fotokopi KTP', cara_pemenuhan: 'unggah', is_wajib: true }]);

        await loginAs(page, email);
        await page.goto('/persyaratan-dokumen');
        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(uniqueSyarat);
        await expect(page.getByText(targetName)).toBeVisible();
        await expect(page.getByText(otherName)).toHaveCount(0);
    });

    test('halaman responsif di viewport smartphone dengan badge', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.us94.mobile.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 3);
        const namaSurat = `Surat US94 Mobile ${stamp}`;

        ensureUser({ email, name: 'Warga US94 Mobile', role: 'warga', nik });
        ensureJenisSurat(namaSurat, mixedRows);
        const ids = persyaratanIdsByNama(namaSurat);

        await page.setViewportSize({ width: 390, height: 844 });
        await loginAs(page, email);
        await page.goto('/persyaratan-dokumen');
        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(namaSurat);
        await expect(page.getByText(namaSurat)).toBeVisible();
        await expect(page.locator(`[data-test="persyaratan-dokumen-preview-badge-${ids['Fotokopi KTP']}"]`)).toBeVisible();
        await expect(page.getByRole('button', { name: 'Lihat Detail' }).first()).toBeVisible();
    });

    test('pencarian tanpa hasil menampilkan empty state', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.us94.empty.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)) + 4);

        ensureUser({ email, name: 'Warga US94 Empty', role: 'warga', nik });
        ensureJenisSurat(`Surat US94 Ada ${stamp}`, [
            { nama: 'Fotokopi KTP', cara_pemenuhan: 'unggah', is_wajib: true },
        ]);

        await loginAs(page, email);
        await page.goto('/persyaratan-dokumen');
        await page.locator('[data-test="persyaratan-dokumen-search"]').fill(`TidakAdaHasilUS94${stamp}`);
        await expect(page.locator('[data-test="persyaratan-dokumen-empty"]')).toBeVisible();
    });
});
