import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-5.1 — Notifikasi Otomatis Perubahan Status
 * US-5.2 — Panel Notifikasi In-App
 * US-5.3 — Halaman Status & Riwayat Pengajuan (detail)
 * US-8.4 — Notifikasi setujui digeser ke status diproses (satu pesan)
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
        `'alamat' => 'Jl. E2E Notifikasi No. 1',`,
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
        `'deskripsi' => 'Deskripsi e2e notifikasi',`,
        `'persyaratan_dokumen' => '- Fotokopi KTP\\n- Fotokopi KK',`,
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
}): { pengajuanId: number } {
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
        `\\App\\Models\\DokumenPersyaratan::create(['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'KTP', 'file_path' => $ktpPath]);`,
        `\\App\\Models\\DokumenPersyaratan::create(['pengajuan_id' => $pengajuan->id, 'jenis_dokumen' => 'KK', 'file_path' => $kkPath]);`,
    ].join('');

    runTinker(php);

    const lookupOutput = runTinker([
        `$p = \\App\\Models\\PengajuanSurat::where('nomor_pengajuan', ${JSON.stringify(options.nomorPengajuan)})->first();`,
        `echo json_encode(['pengajuan_id' => $p->id]);`,
    ].join('')).trim();

    try {
        const parsed = JSON.parse(lookupOutput) as { pengajuan_id: number };

        return { pengajuanId: parsed.pengajuan_id };
    } catch {
        throw new Error(`Failed to resolve pengajuan id: ${lookupOutput}`);
    }
}

function countUnreadNotifikasi(userId: number): number {
    const output = runTinker([
        `echo \\App\\Models\\Notifikasi::where('user_id', ${userId})`,
        `->where('status_baca', \\App\\Models\\Notifikasi::STATUS_BELUM)`,
        `->count();`,
    ].join('')).trim();

    return Number(output);
}

function getNotifikasiStatusByPengajuan(pengajuanId: number): string {
    const output = runTinker([
        `$n = \\App\\Models\\Notifikasi::where('pengajuan_id', ${pengajuanId})->latest('id')->first();`,
        `echo $n ? $n->status_baca : '';`,
    ].join('')).trim();

    return output;
}

async function logoutSession(page: import('@playwright/test').Page): Promise<void> {
    await page.context().clearCookies();
}

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

test.describe('US-5.1 Notifikasi Otomatis Perubahan Status', () => {
    test('admin setujui pengajuan memicu notifikasi belum dibaca untuk warga', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.notif.setujui.${stamp}@example.com`;
        const wargaEmail = `warga.notif.setujui.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)));
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 1);
        const namaSurat = `Surat Notif Setujui ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-5501`;

        ensureUser({ email: adminEmail, name: 'Admin Notif Setujui', role: 'admin', nik: adminNik });
        ensureUser({ email: wargaEmail, name: 'Warga Notif Setujui', role: 'warga', nik: wargaNik });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);

        const { pengajuanId } = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: 'Keperluan notifikasi setujui',
            nomorPengajuan,
            ktpFixturePath: path.join(fixturesDir, 'ktp-sample.jpg'),
            kkFixturePath: path.join(fixturesDir, 'kk-sample.png'),
        });

        expect(countUnreadNotifikasi(wargaId)).toBe(0);

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);
        await page.locator('[data-test="verifikasi-detail-setujui-button"]').click();
        await expect(page).toHaveURL(/\/admin\/verifikasi$/);

        expect(countUnreadNotifikasi(wargaId)).toBe(1);

        const pesan = runTinker([
            `$n = \\App\\Models\\Notifikasi::where('user_id', ${wargaId})->latest('id')->first();`,
            `echo $n ? $n->pesan : '';`,
        ].join('')).trim();

        expect(pesan).toBe(
            `Pengajuan ${namaSurat} Anda (#${nomorPengajuan}) sedang diproses. Surat Anda sedang disiapkan.`,
        );
    });

    test('admin buka detail diajukan tidak memicu notifikasi', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.notif.noauto.${stamp}@example.com`;
        const wargaEmail = `warga.notif.noauto.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 2);
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 3);
        const namaSurat = `Surat Notif No Auto ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-5502`;

        ensureUser({ email: adminEmail, name: 'Admin Notif No Auto', role: 'admin', nik: adminNik });
        ensureUser({ email: wargaEmail, name: 'Warga Notif No Auto', role: 'warga', nik: wargaNik });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);

        const { pengajuanId } = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: 'Keperluan tanpa notifikasi buka detail',
            nomorPengajuan,
            ktpFixturePath: path.join(fixturesDir, 'ktp-sample.jpg'),
            kkFixturePath: path.join(fixturesDir, 'kk-sample.png'),
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);
        await expect(page.locator('[data-test="verifikasi-detail-status"]')).toContainText('Diajukan');

        expect(countUnreadNotifikasi(wargaId)).toBe(0);
    });
});

test.describe('US-5.2 Panel Notifikasi In-App', () => {
    test('warga melihat badge notifikasi dan klik menandai dibaca lalu ke detail', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.notif.panel.${stamp}@example.com`;
        const wargaEmail = `warga.notif.panel.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 4);
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 5);
        const namaSurat = `Surat Notif Panel ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-5503`;
        const alasan = 'Dokumen tidak lengkap untuk e2e';

        ensureUser({ email: adminEmail, name: 'Admin Notif Panel', role: 'admin', nik: adminNik });
        ensureUser({ email: wargaEmail, name: 'Warga Notif Panel', role: 'warga', nik: wargaNik });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);

        const { pengajuanId } = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: 'Keperluan notifikasi panel',
            nomorPengajuan,
            ktpFixturePath: path.join(fixturesDir, 'ktp-sample.jpg'),
            kkFixturePath: path.join(fixturesDir, 'kk-sample.png'),
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);
        await page.locator('[data-test="verifikasi-detail-tolak-button"]').click();
        await page.locator('[data-test="verifikasi-detail-catatan-admin"]').fill(alasan);
        await page.locator('[data-test="verifikasi-detail-tolak-confirm"]').click();
        await expect(page).toHaveURL(/\/admin\/verifikasi$/);

        await logoutSession(page);
        await loginAs(page, wargaEmail);
        await expect(page).toHaveURL(/\/dashboard$/);

        await expect(page.locator('[data-test="panel-notifikasi-badge"]')).toBeVisible();
        await page.locator('[data-test="panel-notifikasi-toggle"]').click();

        const notifItem = page.locator('[data-test^="panel-notifikasi-item-"]').filter({ hasText: 'ditolak' }).first();
        await expect(notifItem).toBeVisible();
        await notifItem.click();

        await expect(page).toHaveURL(new RegExp(`/pengajuan-surat/detail/${pengajuanId}$`));
        await expect(page.locator('[data-test="detail-pengajuan-warga-heading"]')).toBeVisible();
        await expect(page.locator('[data-test="detail-pengajuan-warga-catatan"]')).toContainText(alasan);

        expect(getNotifikasiStatusByPengajuan(pengajuanId)).toBe('dibaca');
    });
});

test.describe('US-5.3 Halaman Status & Riwayat Pengajuan', () => {
    test('warga dapat filter riwayat dan buka detail dari baris tabel', async ({ page }) => {
        const stamp = Date.now();
        const wargaEmail = `warga.riwayat.detail.${stamp}@example.com`;
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 6);
        const namaSurat = `Surat Riwayat Detail ${stamp}`;
        const nomorDisetujui = `PJ-${String(stamp).slice(-8)}-5504`;
        const nomorDitolak = `PJ-${String(stamp).slice(-8)}-5505`;
        const catatan = 'Alasan penolakan e2e riwayat';

        ensureUser({ email: wargaEmail, name: 'Warga Riwayat Detail', role: 'warga', nik: wargaNik });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);

        runTinker([
            `\\App\\Models\\PengajuanSurat::create([`,
            `'user_id' => ${wargaId},`,
            `'jenis_surat_id' => ${jenisSuratId},`,
            `'nomor_pengajuan' => ${JSON.stringify(nomorDisetujui)},`,
            `'keperluan' => 'Keperluan disetujui riwayat',`,
            `'status' => \\App\\Models\\PengajuanSurat::STATUS_DISETUJUI,`,
            `'tanggal_pengajuan' => now()->toDateString(),`,
            `]);`,
            `\\App\\Models\\PengajuanSurat::create([`,
            `'user_id' => ${wargaId},`,
            `'jenis_surat_id' => ${jenisSuratId},`,
            `'nomor_pengajuan' => ${JSON.stringify(nomorDitolak)},`,
            `'keperluan' => 'Keperluan ditolak riwayat',`,
            `'status' => \\App\\Models\\PengajuanSurat::STATUS_DITOLAK,`,
            `'catatan_admin' => ${JSON.stringify(catatan)},`,
            `'tanggal_pengajuan' => now()->toDateString(),`,
            `]);`,
        ].join(''));

        const lookupOutput = runTinker([
            `$p = \\App\\Models\\PengajuanSurat::where('nomor_pengajuan', ${JSON.stringify(nomorDitolak)})->first();`,
            `echo $p->id;`,
        ].join('')).trim();

        const ditolakId = Number(lookupOutput);

        await loginAs(page, wargaEmail);
        await page.goto('/riwayat-pengajuan');

        await expect(page.locator('[data-test="riwayat-pengajuan-heading"]')).toBeVisible();
        await expect(page.getByText(nomorDisetujui)).toBeVisible();
        await expect(page.getByText(nomorDitolak)).toBeVisible();
        await expect(page.getByText(catatan)).toBeVisible();

        await page.locator('[data-test="riwayat-pengajuan-status-filter"]').selectOption('ditolak');
        await expect(page.getByText(nomorDisetujui)).toHaveCount(0);
        await expect(page.getByText(nomorDitolak)).toBeVisible();

        await page.locator(`[data-test="riwayat-pengajuan-detail-${ditolakId}"]`).click();
        await expect(page).toHaveURL(new RegExp(`/pengajuan-surat/detail/${ditolakId}$`));
        await expect(page.locator('[data-test="detail-pengajuan-warga-catatan"]')).toContainText(catatan);
    });
});
