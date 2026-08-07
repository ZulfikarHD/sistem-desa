import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-9.5 — Checklist fisik di detail verifikasi admin
 * Happy: online uploads + physical checklist + optional empty badge.
 * Edge: no physical syarat empty state; setujui/tolak still available.
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const fixturesDir = path.join(projectRoot, 'e2e', 'fixtures');

type PersyaratanRowInput = {
    nama: string;
    cara_pemenuhan: 'unggah' | 'bawa_kantor' | 'info';
    is_wajib: boolean;
};

function runTinker(php: string): string {
    return execFileSync('php', ['artisan', 'tinker', '--execute', php], {
        cwd: projectRoot,
        encoding: 'utf8',
    });
}

function uniqueNik(suffix: number): string {
    return `3208080808${String(suffix).padStart(6, '0')}`;
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
        `'alamat' => 'Jl. E2E US-9.5 No. 1',`,
        `'role' => ${JSON.stringify(options.role)},`,
        `'password' => ${JSON.stringify(password)},`,
        `'email_verified_at' => now(),`,
        `]`,
        `);`,
    ].join('');

    runTinker(php);
}

function getUserIdByEmail(email: string): number {
    const output = runTinker(`echo \\App\\Models\\User::where('email', ${JSON.stringify(email)})->value('id');`).trim();
    const id = Number(output);
    if (!id) {
        throw new Error(`Failed to resolve user id for ${email}: ${output}`);
    }

    return id;
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

function ensureJenisSurat(namaSurat: string, rows: PersyaratanRowInput[]): number {
    const php = [
        `$model = \\App\\Models\\JenisSurat::query()->updateOrCreate(`,
        `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
        `['deskripsi' => 'Deskripsi e2e US-9.5', 'persyaratan_dokumen' => 'placeholder']`,
        `);`,
        `$model->syncPersyaratan(${rowsToPhpArray(rows)});`,
        `echo $model->id;`,
    ].join('');

    const output = runTinker(php).trim();
    const id = Number(output);
    if (!id) {
        throw new Error(`Failed to ensure jenis surat ${namaSurat}: ${output}`);
    }

    return id;
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

    const output = runTinker(php);
    const match = output.match(/\{[\s\S]*\}/);
    if (!match) {
        throw new Error(`Gagal membaca persyaratan IDs untuk ${namaSurat}: ${output}`);
    }

    return JSON.parse(match[0]) as Record<string, number>;
}

function ensurePengajuanWithMixedDokumen(options: {
    userId: number;
    jenisSuratId: number;
    nomorPengajuan: string;
    ktpSyaratId: number;
    ktpFixturePath: string;
    includeOptionalUpload?: boolean;
    optionalSyaratId?: number;
}): number {
    const includeOptional = options.includeOptionalUpload === true && options.optionalSyaratId;
    const php = [
        `$pengajuan = \\App\\Models\\PengajuanSurat::query()->updateOrCreate(`,
        `['nomor_pengajuan' => ${JSON.stringify(options.nomorPengajuan)}],`,
        `[`,
        `'user_id' => ${options.userId},`,
        `'jenis_surat_id' => ${options.jenisSuratId},`,
        `'keperluan' => 'Keperluan e2e US-9.5 checklist fisik',`,
        `'status' => 'diajukan',`,
        `'tanggal_pengajuan' => '2020-01-01',`,
        `]`,
        `);`,
        `$dir = 'pengajuan-dokumen/' . $pengajuan->id;`,
        `\\Illuminate\\Support\\Facades\\Storage::disk('local')->makeDirectory($dir);`,
        `$ktpPath = $dir . '/ktp.jpg';`,
        `\\Illuminate\\Support\\Facades\\Storage::disk('local')->put($ktpPath, file_get_contents(${JSON.stringify(options.ktpFixturePath)}));`,
        `\\App\\Models\\DokumenPersyaratan::query()->updateOrCreate(`,
        `['pengajuan_id' => $pengajuan->id, 'jenis_surat_persyaratan_id' => ${options.ktpSyaratId}],`,
        `['jenis_dokumen' => 'Fotokopi KTP', 'file_path' => $ktpPath]`,
        `);`,
        includeOptional
            ? [
                  `$optPath = $dir . '/npwp.jpg';`,
                  `\\Illuminate\\Support\\Facades\\Storage::disk('local')->put($optPath, file_get_contents(${JSON.stringify(options.ktpFixturePath)}));`,
                  `\\App\\Models\\DokumenPersyaratan::query()->updateOrCreate(`,
                  `['pengajuan_id' => $pengajuan->id, 'jenis_surat_persyaratan_id' => ${options.optionalSyaratId}],`,
                  `['jenis_dokumen' => 'NPWP (jika ada)', 'file_path' => $optPath]`,
                  `);`,
              ].join('')
            : `\\App\\Models\\DokumenPersyaratan::query()->where('pengajuan_id', $pengajuan->id)->where('jenis_surat_persyaratan_id', ${options.optionalSyaratId ?? 0})->delete();`,
        `echo $pengajuan->id;`,
    ].join('');

    const output = runTinker(php).trim();
    const id = Number(output);
    if (!id) {
        throw new Error(`Failed to create pengajuan: ${output}`);
    }

    return id;
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

test.describe('US-9.5 Checklist Fisik Verifikasi Admin', () => {
    test('admin melihat unggahan online, opsional kosong, dan checklist fisik', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.us95.${stamp}@example.com`;
        const wargaEmail = `warga.us95.${stamp}@example.com`;
        const namaSurat = `Surat US95 Checklist ${stamp}`;
        const nomor = `PJ-US95-${stamp}`;
        const ktpFixture = path.join(fixturesDir, 'ktp-sample.jpg');

        ensureUser({
            email: adminEmail,
            name: 'Admin US95',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga US95',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 1),
        });

        const jenisSuratId = ensureJenisSurat(namaSurat, mixedRows);
        const ids = persyaratanIdsByNama(namaSurat);
        const wargaId = getUserIdByEmail(wargaEmail);
        const pengajuanId = ensurePengajuanWithMixedDokumen({
            userId: wargaId,
            jenisSuratId,
            nomorPengajuan: nomor,
            ktpSyaratId: ids['Fotokopi KTP'],
            ktpFixturePath: ktpFixture,
            includeOptionalUpload: false,
            optionalSyaratId: ids['NPWP (jika ada)'],
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);

        await expect(page.locator('[data-test="verifikasi-detail-heading"]')).toBeVisible();
        await expect(page.locator('[data-test="verifikasi-detail-dokumen-online"]')).toContainText('Diunggah online');
        await expect(page.locator('[data-test="verifikasi-detail-checklist-fisik"]')).toContainText(
            'Harus dicek / dibawa ke kantor',
        );
        await expect(
            page.locator(`[data-test="verifikasi-detail-dokumen-optional-empty-${ids['NPWP (jika ada)']}"]`),
        ).toContainText('Tidak diunggah — diperbolehkan');
        await expect(
            page.locator(`[data-test="verifikasi-detail-checklist-fisik-item-${ids['Surat pengantar RT/RW']}"]`),
        ).toBeVisible();
        await expect(page.locator(`[data-test="verifikasi-detail-checklist-fisik-nama-${ids['Surat pengantar RT/RW']}"]`)).toContainText(
            'Surat pengantar RT/RW',
        );
        await expect(page.locator('[data-test="verifikasi-detail-setujui-button"]')).toBeVisible();
        await expect(page.locator('[data-test="verifikasi-detail-tolak-button"]')).toBeVisible();
    });

    test('checklist fisik kosong bila tidak ada syarat bawa kantor', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.us95.empty.${stamp}@example.com`;
        const wargaEmail = `warga.us95.empty.${stamp}@example.com`;
        const namaSurat = `Surat US95 No Fisik ${stamp}`;
        const nomor = `PJ-US95E-${stamp}`;
        const ktpFixture = path.join(fixturesDir, 'ktp-sample.jpg');

        ensureUser({
            email: adminEmail,
            name: 'Admin US95 Empty',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 2),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga US95 Empty',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6)) + 3),
        });

        const jenisSuratId = ensureJenisSurat(namaSurat, [
            { nama: 'Fotokopi KTP', cara_pemenuhan: 'unggah', is_wajib: true },
        ]);
        const ids = persyaratanIdsByNama(namaSurat);
        const wargaId = getUserIdByEmail(wargaEmail);
        const pengajuanId = ensurePengajuanWithMixedDokumen({
            userId: wargaId,
            jenisSuratId,
            nomorPengajuan: nomor,
            ktpSyaratId: ids['Fotokopi KTP'],
            ktpFixturePath: ktpFixture,
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);

        await expect(page.locator('[data-test="verifikasi-detail-checklist-fisik-empty"]')).toBeVisible();
        await expect(page.locator('[data-test="verifikasi-detail-setujui-button"]')).toBeVisible();
    });
});
