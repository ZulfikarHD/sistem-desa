import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-7.2 — Generate Surat PDF Saat Masuk Diproses
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const fixturesDir = path.join(projectRoot, 'e2e', 'fixtures');

function runTinker(php: string): string {
    return execFileSync('php', ['artisan', 'tinker', '--execute', php], {
        cwd: projectRoot,
        encoding: 'utf8',
    });
}

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
        `'alamat' => 'Jl. E2E Generate Surat No. 1',`,
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

function ensureJenisSurat(namaSurat: string): void {
    const php = [
        `\\App\\Models\\JenisSurat::updateOrCreate(`,
        `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
        `[`,
        `'deskripsi' => 'Deskripsi e2e generate surat',`,
        `'persyaratan_dokumen' => "- Fotokopi KTP\\n- Fotokopi KK",`,
        `]`,
        `);`,
    ].join('');

    runTinker(php);
}

function getJenisSuratIdByName(namaSurat: string): number {
    const output = runTinker(
        `echo \\App\\Models\\JenisSurat::where('nama_surat', ${JSON.stringify(namaSurat)})->value('id');`,
    ).trim();
    const id = Number(output);
    if (!id) {
        throw new Error(`Failed to resolve jenis surat id for ${namaSurat}: ${output}`);
    }

    return id;
}

function ensurePengajuanDiajukan(options: {
    userId: number;
    jenisSuratId: number;
    keperluan: string;
    nomorPengajuan: string;
}): number {
    const php = [
        `$pengajuan = \\App\\Models\\PengajuanSurat::create([`,
        `'user_id' => ${options.userId},`,
        `'jenis_surat_id' => ${options.jenisSuratId},`,
        `'nomor_pengajuan' => ${JSON.stringify(options.nomorPengajuan)},`,
        `'keperluan' => ${JSON.stringify(options.keperluan)},`,
        `'status' => \\App\\Models\\PengajuanSurat::STATUS_DIAJUKAN,`,
        `'tanggal_pengajuan' => now()->toDateString(),`,
        `]);`,
        `$dir = 'pengajuan-dokumen/' . $pengajuan->id;`,
        `$ktpPath = $dir . '/ktp_e2e.jpg';`,
        `$kkPath = $dir . '/kk_e2e.png';`,
        `\\Illuminate\\Support\\Facades\\Storage::disk('local')->put($ktpPath, file_get_contents(${JSON.stringify(path.join(fixturesDir, 'ktp-sample.jpg'))}));`,
        `\\Illuminate\\Support\\Facades\\Storage::disk('local')->put($kkPath, file_get_contents(${JSON.stringify(path.join(fixturesDir, 'kk-sample.png'))}));`,
        `\\App\\Models\\DokumenPersyaratan::create(['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'KTP', 'file_path' => $ktpPath]);`,
        `\\App\\Models\\DokumenPersyaratan::create(['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'KK', 'file_path' => $kkPath]);`,
        `echo $pengajuan->id;`,
    ].join('');

    const id = Number(runTinker(php).trim());
    if (!id) {
        throw new Error(`Failed to create pengajuan: ${options.nomorPengajuan}`);
    }

    return id;
}

function getPengajuanStatus(nomorPengajuan: string): string {
    return runTinker([
        `$p = \\App\\Models\\PengajuanSurat::where('nomor_pengajuan', ${JSON.stringify(nomorPengajuan)})->first();`,
        `echo $p ? $p->status : '';`,
    ].join('')).trim();
}

function getSuratTerbitPayload(pengajuanId: number): {
    exists: boolean;
    nomor_surat: string | null;
    file_path: string | null;
    qr_token: string | null;
    qr_status: string | null;
    file_exists: boolean;
    pdf_header: string | null;
} {
    const output = runTinker([
        `$s = \\App\\Models\\SuratTerbit::where('pengajuan_id', ${pengajuanId})->first();`,
        `if (! $s) { echo json_encode(['exists' => false, 'nomor_surat' => null, 'file_path' => null, 'qr_token' => null, 'qr_status' => null, 'file_exists' => false, 'pdf_header' => null]); return; }`,
        `$exists = \\Illuminate\\Support\\Facades\\Storage::disk('local')->exists($s->file_path);`,
        `$header = $exists ? substr(\\Illuminate\\Support\\Facades\\Storage::disk('local')->get($s->file_path), 0, 4) : null;`,
        `echo json_encode([`,
        `'exists' => true,`,
        `'nomor_surat' => $s->nomor_surat,`,
        `'file_path' => $s->file_path,`,
        `'qr_token' => $s->qr_token,`,
        `'qr_status' => $s->qr_status,`,
        `'file_exists' => $exists,`,
        `'pdf_header' => $header,`,
        `]);`,
    ].join('')).trim();

    return JSON.parse(output);
}

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

test.describe('US-7.2 Generate Surat PDF Saat Diproses', () => {
    test('setujui menghasilkan PDF, nomor surat, dan QR valid di storage', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.generate.surat.${stamp}@example.com`;
        const wargaEmail = `warga.generate.surat.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)));
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 1);
        const namaSurat = `Surat Keterangan Domisili E2E ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-7201`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Generate Surat',
            role: 'admin',
            nik: adminNik,
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Generate Surat',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);
        const pengajuanId = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: `Keperluan generate PDF ${stamp}`,
            nomorPengajuan,
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);
        await expect(page.locator('[data-test="verifikasi-detail-setujui-button"]')).toBeVisible();
        await page.locator('[data-test="verifikasi-detail-setujui-button"]').click();
        await expect(page).toHaveURL(/\/admin\/verifikasi$/);

        expect(getPengajuanStatus(nomorPengajuan)).toBe('diproses');

        const surat = getSuratTerbitPayload(pengajuanId);
        expect(surat.exists).toBe(true);
        expect(surat.nomor_surat).toMatch(/^470\/\d+\/DS-WDN\/[IVX]+\/\d{4}$/);
        expect(surat.qr_status).toBe('valid');
        expect(surat.qr_token).toHaveLength(64);
        expect(surat.qr_token).not.toContain(wargaNik);
        expect(surat.file_exists).toBe(true);
        expect(surat.pdf_header).toBe('%PDF');
        expect(surat.file_path).toBe(`surat-terbit/${pengajuanId}/surat.pdf`);
    });

    test('tolak tidak menghasilkan PDF atau QR', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.generate.tolak.${stamp}@example.com`;
        const wargaEmail = `warga.generate.tolak.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 2);
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 3);
        const namaSurat = `Surat Keterangan Usaha Tolak E2E ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-7202`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Generate Tolak',
            role: 'admin',
            nik: adminNik,
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Generate Tolak',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);
        const pengajuanId = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: `Keperluan tolak tanpa PDF ${stamp}`,
            nomorPengajuan,
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);
        await page.locator('[data-test="verifikasi-detail-tolak-button"]').click();
        await page.locator('[data-test="verifikasi-detail-catatan-admin"]').fill(
            'Dokumen tidak lengkap untuk penerbitan surat',
        );
        await page.locator('[data-test="verifikasi-detail-tolak-confirm"]').click();
        await expect(page).toHaveURL(/\/admin\/verifikasi$/);

        expect(getPengajuanStatus(nomorPengajuan)).toBe('ditolak');

        const surat = getSuratTerbitPayload(pengajuanId);
        expect(surat.exists).toBe(false);
        expect(surat.file_exists).toBe(false);
    });
});
