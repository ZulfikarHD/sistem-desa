import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-4.1 — Daftar Pengajuan Menunggu Verifikasi (Admin)
 * US-4.2 — Detail Pengajuan & Pratinjau Dokumen
 * US-4.3 — Setujui / Tolak Pengajuan
 * US-4.4 — Transisi Status Otomatis ke "Diproses"
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
    return `3206060606${String(suffix).padStart(6, '0')}`;
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
        `'alamat' => 'Jl. E2E Verifikasi No. 1',`,
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

function ensureJenisSurat(
    namaSurat: string,
    persyaratanDokumen = '- Fotokopi KTP\n- Fotokopi KK',
): void {
    const php = [
        `\\App\\Models\\JenisSurat::updateOrCreate(`,
        `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
        `[`,
        `'deskripsi' => 'Deskripsi e2e verifikasi',`,
        `'persyaratan_dokumen' => ${JSON.stringify(persyaratanDokumen)},`,
        `]`,
        `);`,
    ].join('');

    runTinker(php);
}

function getJenisSuratIdByName(namaSurat: string): number {
    const output = runTinker(`echo \\App\\Models\\JenisSurat::where('nama_surat', ${JSON.stringify(namaSurat)})->value('id');`).trim();

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
    ktpFixturePath: string;
    kkFixturePath: string;
}): { pengajuanId: number; ktpDokumenId: number; kkDokumenId: number } {
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
        `\\Illuminate\\Support\\Facades\\Storage::disk('local')->put($ktpPath, file_get_contents(${JSON.stringify(options.ktpFixturePath)}));`,
        `\\Illuminate\\Support\\Facades\\Storage::disk('local')->put($kkPath, file_get_contents(${JSON.stringify(options.kkFixturePath)}));`,
        `$ktp = \\App\\Models\\DokumenPersyaratan::create(['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'KTP', 'file_path' => $ktpPath]);`,
        `$kk = \\App\\Models\\DokumenPersyaratan::create(['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'KK', 'file_path' => $kkPath]);`,
    ].join('');

    runTinker(php);

    const lookupOutput = runTinker([
        `$p = \\App\\Models\\PengajuanSurat::where('nomor_pengajuan', ${JSON.stringify(options.nomorPengajuan)})->first();`,
        `$ktp = \\App\\Models\\DokumenPersyaratan::where('pengajuan_id', $p->id)->where('jenis_dokumen', 'KTP')->first();`,
        `$kk = \\App\\Models\\DokumenPersyaratan::where('pengajuan_id', $p->id)->where('jenis_dokumen', 'KK')->first();`,
        `echo json_encode(['pengajuan_id' => $p->id, 'ktp_dokumen_id' => $ktp->id, 'kk_dokumen_id' => $kk->id]);`,
    ].join('')).trim();

    try {
        const parsed = JSON.parse(lookupOutput) as {
            pengajuan_id: number;
            ktp_dokumen_id: number;
            kk_dokumen_id: number;
        };

        return {
            pengajuanId: parsed.pengajuan_id,
            ktpDokumenId: parsed.ktp_dokumen_id,
            kkDokumenId: parsed.kk_dokumen_id,
        };
    } catch {
        throw new Error(`Failed to resolve pengajuan/dokumen ids: ${lookupOutput}`);
    }
}

function getPengajuanStatusByNomor(nomorPengajuan: string): string {
    const output = runTinker([
        `$p = \\App\\Models\\PengajuanSurat::where('nomor_pengajuan', ${JSON.stringify(nomorPengajuan)})->first();`,
        `echo $p ? $p->status : '';`,
    ].join('')).trim();

    return output;
}

function countLogVerifikasi(pengajuanId: number, aksi: 'setujui' | 'tolak'): number {
    const output = runTinker([
        `echo \\App\\Models\\LogVerifikasi::where('pengajuan_id', ${pengajuanId})`,
        `->where('aksi', ${JSON.stringify(aksi)})`,
        `->count();`,
    ].join('')).trim();

    return Number(output);
}

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

test.describe('US-4.1 Daftar Pengajuan Menunggu Verifikasi', () => {
    test('guest yang mengakses halaman verifikasi diarahkan ke login', async ({ page }) => {
        await page.goto('/admin/verifikasi');
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('input[name="email"]')).toBeVisible();
    });

    test('warga yang mengakses halaman verifikasi mendapat 403', async ({ page }) => {
        const stamp = Date.now();
        const email = `warga.verifikasi.${stamp}@example.com`;
        const nik = uniqueNik(Number(String(stamp).slice(-6)));

        ensureUser({
            email,
            name: 'Warga Verifikasi Forbid',
            role: 'warga',
            nik,
        });

        await loginAs(page, email);
        await expect(page).toHaveURL(/\/dashboard$/);

        const response = await page.goto('/admin/verifikasi');
        expect(response?.status()).toBe(403);
        await expect(page.locator('[data-test="verifikasi-pengajuan-heading"]')).toHaveCount(0);
    });

    test('admin melihat daftar pengajuan diajukan dengan kolom wajib', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.verifikasi.list.${stamp}@example.com`;
        const wargaEmail = `warga.verifikasi.list.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)));
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 1);
        const namaSurat = `Surat Verifikasi List ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-4401`;
        const keperluan = `Keperluan verifikasi list ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Verifikasi List',
            role: 'admin',
            nik: adminNik,
        });

        ensureUser({
            email: wargaEmail,
            name: 'Warga Verifikasi List',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);

        const { pengajuanId } = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan,
            nomorPengajuan,
            ktpFixturePath: path.join(fixturesDir, 'ktp-sample.jpg'),
            kkFixturePath: path.join(fixturesDir, 'kk-sample.png'),
        });

        await loginAs(page, adminEmail);
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        await page.goto('/admin/verifikasi');
        await expect(page.locator('[data-test="verifikasi-pengajuan-heading"]')).toBeVisible();
        await expect(page.locator('[data-test="verifikasi-pengajuan-status-filter"]')).toHaveValue('diajukan');
        await expect(page.locator(`[data-test="verifikasi-pengajuan-nomor-${pengajuanId}"]`)).toContainText(nomorPengajuan);
        await expect(page.locator(`[data-test="verifikasi-pengajuan-warga-${pengajuanId}"]`)).toContainText('Warga Verifikasi List');
        await expect(page.locator(`[data-test="verifikasi-pengajuan-jenis-${pengajuanId}"]`)).toContainText(namaSurat);
    });

    test('admin tidak melihat pengajuan non-diajukan saat filter default', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.verifikasi.filter.${stamp}@example.com`;
        const wargaEmail = `warga.verifikasi.filter.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 2);
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 3);
        const namaSurat = `Surat Verifikasi Filter ${stamp}`;
        const nomorDiajukan = `PJ-${String(stamp).slice(-8)}-4402`;
        const nomorDisetujui = `PJ-${String(stamp).slice(-8)}-4403`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Verifikasi Filter',
            role: 'admin',
            nik: adminNik,
        });

        ensureUser({
            email: wargaEmail,
            name: 'Warga Verifikasi Filter',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);

        ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: 'Keperluan diajukan',
            nomorPengajuan: nomorDiajukan,
            ktpFixturePath: path.join(fixturesDir, 'ktp-sample.jpg'),
            kkFixturePath: path.join(fixturesDir, 'kk-sample.png'),
        });

        const phpDisetujui = [
            `\\App\\Models\\PengajuanSurat::create([`,
            `'user_id' => ${wargaId},`,
            `'jenis_surat_id' => ${jenisSuratId},`,
            `'nomor_pengajuan' => ${JSON.stringify(nomorDisetujui)},`,
            `'keperluan' => 'Keperluan disetujui',`,
            `'status' => \\App\\Models\\PengajuanSurat::STATUS_DISETUJUI,`,
            `'tanggal_pengajuan' => now()->toDateString(),`,
            `]);`,
        ].join('');

        runTinker(phpDisetujui);

        await loginAs(page, adminEmail);
        await page.goto('/admin/verifikasi');

        await expect(page.getByText(nomorDiajukan)).toBeVisible();
        await expect(page.getByText(nomorDisetujui)).toHaveCount(0);
    });
});

test.describe('US-4.2 Detail Pengajuan & Pratinjau Dokumen', () => {
    test('admin dapat membuka detail pengajuan dari baris daftar', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.verifikasi.detail.${stamp}@example.com`;
        const wargaEmail = `warga.verifikasi.detail.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 4);
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 5);
        const namaSurat = `Surat Verifikasi Detail ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-4404`;
        const keperluan = `Keperluan detail verifikasi ${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Verifikasi Detail',
            role: 'admin',
            nik: adminNik,
        });

        ensureUser({
            email: wargaEmail,
            name: 'Warga Verifikasi Detail',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);

        const { pengajuanId } = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan,
            nomorPengajuan,
            ktpFixturePath: path.join(fixturesDir, 'ktp-sample.jpg'),
            kkFixturePath: path.join(fixturesDir, 'kk-sample.png'),
        });

        await loginAs(page, adminEmail);
        await page.goto('/admin/verifikasi');

        await page.locator(`[data-test="verifikasi-pengajuan-row-${pengajuanId}"]`).click();

        await expect(page).toHaveURL(new RegExp(`/admin/verifikasi/${pengajuanId}$`));
        await expect(page.locator('[data-test="verifikasi-detail-heading"]')).toBeVisible();
        await expect(page.locator('[data-test="verifikasi-detail-nomor"]')).toContainText(nomorPengajuan);
        await expect(page.locator('[data-test="verifikasi-detail-nama-warga"]')).toContainText('Warga Verifikasi Detail');
        await expect(page.locator('[data-test="verifikasi-detail-jenis-surat"]')).toContainText(namaSurat);
        await expect(page.locator('[data-test="verifikasi-detail-keperluan"]')).toContainText(keperluan);
    });

    test('admin melihat pratinjau dokumen dan tombol setujui/tolak di halaman detail', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.verifikasi.preview.${stamp}@example.com`;
        const wargaEmail = `warga.verifikasi.preview.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 6);
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 7);
        const namaSurat = `Surat Verifikasi Preview ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-4405`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Verifikasi Preview',
            role: 'admin',
            nik: adminNik,
        });

        ensureUser({
            email: wargaEmail,
            name: 'Warga Verifikasi Preview',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);

        const { pengajuanId, ktpDokumenId } = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: 'Keperluan preview dokumen',
            nomorPengajuan,
            ktpFixturePath: path.join(fixturesDir, 'ktp-sample.jpg'),
            kkFixturePath: path.join(fixturesDir, 'kk-sample.png'),
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);

        await expect(page.locator(`[data-test="verifikasi-detail-dokumen-preview-${ktpDokumenId}"]`)).toBeVisible({ timeout: 15_000 });
        await expect(page.locator('[data-test="verifikasi-detail-setujui-button"]')).toBeVisible();
        await expect(page.locator('[data-test="verifikasi-detail-tolak-button"]')).toBeVisible();
        await expect(page.locator(`[data-test="verifikasi-detail-dokumen-download-${ktpDokumenId}"]`)).toBeVisible();
    });
});

test.describe('US-4.4 Transisi Status ke Diproses', () => {
    test('status berubah dari diajukan ke diproses saat admin membuka detail pertama kali', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.verifikasi.diproses.${stamp}@example.com`;
        const wargaEmail = `warga.verifikasi.diproses.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 8);
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 9);
        const namaSurat = `Surat Verifikasi Diproses ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-4406`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Verifikasi Diproses',
            role: 'admin',
            nik: adminNik,
        });

        ensureUser({
            email: wargaEmail,
            name: 'Warga Verifikasi Diproses',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);

        const { pengajuanId } = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: 'Keperluan transisi diproses',
            nomorPengajuan,
            ktpFixturePath: path.join(fixturesDir, 'ktp-sample.jpg'),
            kkFixturePath: path.join(fixturesDir, 'kk-sample.png'),
        });

        expect(getPengajuanStatusByNomor(nomorPengajuan)).toBe('diajukan');

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);

        await expect(page.locator('[data-test="verifikasi-detail-status"]')).toContainText('Diproses');
        expect(getPengajuanStatusByNomor(nomorPengajuan)).toBe('diproses');

        await page.reload();
        await expect(page.locator('[data-test="verifikasi-detail-status"]')).toContainText('Diproses');
        expect(getPengajuanStatusByNomor(nomorPengajuan)).toBe('diproses');
    });
});

test.describe('US-4.3 Setujui / Tolak Pengajuan', () => {
    test('admin dapat menyetujui pengajuan dan pengajuan hilang dari daftar diajukan', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.verifikasi.setujui.${stamp}@example.com`;
        const wargaEmail = `warga.verifikasi.setujui.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 10);
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 11);
        const namaSurat = `Surat Verifikasi Setujui ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-4407`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Verifikasi Setujui',
            role: 'admin',
            nik: adminNik,
        });

        ensureUser({
            email: wargaEmail,
            name: 'Warga Verifikasi Setujui',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);

        const { pengajuanId } = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: 'Keperluan setujui e2e',
            nomorPengajuan,
            ktpFixturePath: path.join(fixturesDir, 'ktp-sample.jpg'),
            kkFixturePath: path.join(fixturesDir, 'kk-sample.png'),
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);

        await expect(page.locator('[data-test="verifikasi-detail-setujui-button"]')).toBeVisible();
        await page.locator('[data-test="verifikasi-detail-setujui-button"]').click();

        await expect(page).toHaveURL(/\/admin\/verifikasi$/);
        await expect(page.locator(`[data-test="verifikasi-pengajuan-nomor-${pengajuanId}"]`)).toHaveCount(0);

        expect(getPengajuanStatusByNomor(nomorPengajuan)).toBe('disetujui');
        expect(countLogVerifikasi(pengajuanId, 'setujui')).toBe(1);
    });

    test('admin wajib mengisi alasan saat menolak pengajuan', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.verifikasi.tolak.fail.${stamp}@example.com`;
        const wargaEmail = `warga.verifikasi.tolak.fail.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 12);
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 13);
        const namaSurat = `Surat Verifikasi Tolak Fail ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-4408`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Verifikasi Tolak Fail',
            role: 'admin',
            nik: adminNik,
        });

        ensureUser({
            email: wargaEmail,
            name: 'Warga Verifikasi Tolak Fail',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);

        const { pengajuanId } = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: 'Keperluan tolak gagal e2e',
            nomorPengajuan,
            ktpFixturePath: path.join(fixturesDir, 'ktp-sample.jpg'),
            kkFixturePath: path.join(fixturesDir, 'kk-sample.png'),
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);

        await page.locator('[data-test="verifikasi-detail-tolak-button"]').click();
        await expect(page.locator('[data-test="verifikasi-detail-catatan-admin"]')).toBeVisible();
        await page.locator('[data-test="verifikasi-detail-tolak-confirm"]').click();

        await expect(page.locator('[data-test="verifikasi-detail-catatan-admin"]')).toBeVisible();
        await expect(page).toHaveURL(new RegExp(`/admin/verifikasi/${pengajuanId}$`));
        expect(getPengajuanStatusByNomor(nomorPengajuan)).toBe('diproses');
        expect(countLogVerifikasi(pengajuanId, 'tolak')).toBe(0);
    });

    test('admin dapat menolak pengajuan dengan catatan dan pengajuan hilang dari daftar diajukan', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.verifikasi.tolak.${stamp}@example.com`;
        const wargaEmail = `warga.verifikasi.tolak.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 14);
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 15);
        const namaSurat = `Surat Verifikasi Tolak ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-4409`;
        const alasan = 'Dokumen KTP tidak terbaca dengan jelas';

        ensureUser({
            email: adminEmail,
            name: 'Admin Verifikasi Tolak',
            role: 'admin',
            nik: adminNik,
        });

        ensureUser({
            email: wargaEmail,
            name: 'Warga Verifikasi Tolak',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);

        const { pengajuanId } = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: 'Keperluan tolak e2e',
            nomorPengajuan,
            ktpFixturePath: path.join(fixturesDir, 'ktp-sample.jpg'),
            kkFixturePath: path.join(fixturesDir, 'kk-sample.png'),
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);

        await page.locator('[data-test="verifikasi-detail-tolak-button"]').click();
        await expect(page.locator('[data-test="verifikasi-detail-catatan-admin"]')).toBeVisible();
        await page.locator('[data-test="verifikasi-detail-catatan-admin"]').fill(alasan);
        await page.locator('[data-test="verifikasi-detail-tolak-confirm"]').click();

        await expect(page).toHaveURL(/\/admin\/verifikasi$/);
        await expect(page.locator(`[data-test="verifikasi-pengajuan-nomor-${pengajuanId}"]`)).toHaveCount(0);

        expect(getPengajuanStatusByNomor(nomorPengajuan)).toBe('ditolak');
        expect(countLogVerifikasi(pengajuanId, 'tolak')).toBe(1);
    });
});
