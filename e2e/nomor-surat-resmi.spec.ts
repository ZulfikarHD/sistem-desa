import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-7.3 — Nomor Surat Resmi Otomatis
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
        `'alamat' => 'Jl. E2E Nomor Surat No. 1',`,
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
        `'deskripsi' => 'Deskripsi e2e nomor surat',`,
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

function getSuratNomorPayload(pengajuanId: number): {
    exists: boolean;
    nomor_surat: string | null;
    nomor_pengajuan: string | null;
    tanggal_terbit: string | null;
    file_exists: boolean;
    pdf_header: string | null;
    template_contains_nomor: boolean;
    pdf_contains_kode: boolean;
} {
    const output = runTinker([
        `$p = \\App\\Models\\PengajuanSurat::with(['user', 'jenisSurat'])->find(${pengajuanId});`,
        `$s = $p?->suratTerbit;`,
        `if (! $s) {`,
        `echo json_encode(['exists' => false, 'nomor_surat' => null, 'nomor_pengajuan' => $p?->nomor_pengajuan, 'tanggal_terbit' => null, 'file_exists' => false, 'pdf_header' => null, 'template_contains_nomor' => false, 'pdf_contains_kode' => false]);`,
        `return;`,
        `}`,
        `$exists = \\Illuminate\\Support\\Facades\\Storage::disk('local')->exists($s->file_path);`,
        `$pdf = $exists ? \\Illuminate\\Support\\Facades\\Storage::disk('local')->get($s->file_path) : '';`,
        `$html = view(\\App\\Models\\SuratTerbit::resolveTemplateView(), [`,
        `'pengajuan' => $p,`,
        `'pemohon' => $p->user,`,
        `'jenisSurat' => $p->jenisSurat,`,
        `'nomorSurat' => $s->nomor_surat,`,
        `'tanggalTerbit' => $s->tanggal_terbit,`,
        `'tanggalPengambilan' => $s->tanggal_pengambilan,`,
        `'jamKerjaLabel' => $s->jam_kerja_label,`,
        `'qrDataUri' => 'data:image/png;base64,xx',`,
        `'desa' => \\App\\Models\\PengaturanDesa::untukSurat(),`,
        `])->render();`,
        `echo json_encode([`,
        `'exists' => true,`,
        `'nomor_surat' => $s->nomor_surat,`,
        `'nomor_pengajuan' => $p->nomor_pengajuan,`,
        `'tanggal_terbit' => optional($s->tanggal_terbit)->toDateString(),`,
        `'file_exists' => $exists,`,
        `'pdf_header' => $exists ? substr($pdf, 0, 4) : null,`,
        `'template_contains_nomor' => str_contains($html, $s->nomor_surat),`,
        `'pdf_contains_kode' => str_contains($pdf, (string) (\\App\\Models\\PengaturanDesa::untukSurat()['kode_klasifikasi'] ?? '470')),`,
        `]);`,
    ].join('')).trim();

    return JSON.parse(output);
}

function romanMonthForNow(): string {
    return runTinker(`echo \\App\\Models\\SuratTerbit::bulanRomawi((int) now()->format('n'));`).trim();
}

function currentYear(): string {
    return runTinker(`echo now()->format('Y');`).trim();
}

async function loginAs(page: import('@playwright/test').Page, email: string, password = 'password'): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('[data-test="login-button"]').click();
}

test.describe('US-7.3 Nomor Surat Resmi Otomatis', () => {
    test('setujui menghasilkan nomor resmi berformat administrasi desa dan tercetak di template PDF', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.nomor.surat.${stamp}@example.com`;
        const wargaEmail = `warga.nomor.surat.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)));
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 1);
        const namaSurat = `Surat Keterangan Domisili Nomor E2E ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-7301`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Nomor Surat',
            role: 'admin',
            nik: adminNik,
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Nomor Surat',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);
        const pengajuanId = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: `Keperluan nomor surat ${stamp}`,
            nomorPengajuan,
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);
        await expect(page.locator('[data-test="verifikasi-detail-setujui-button"]')).toBeVisible();
        await page.locator('[data-test="verifikasi-detail-setujui-button"]').click();
        await expect(page).toHaveURL(/\/admin\/verifikasi$/);

        const roman = romanMonthForNow();
        const year = currentYear();
        const surat = getSuratNomorPayload(pengajuanId);

        expect(surat.exists).toBe(true);
        expect(surat.nomor_surat).toMatch(new RegExp(`^470\\/\\d+\\/DS-WDN\\/${roman}\\/${year}$`));
        expect(surat.nomor_surat).not.toBe(surat.nomor_pengajuan);
        expect(surat.nomor_surat).not.toMatch(/^PJ-/);
        expect(surat.file_exists).toBe(true);
        expect(surat.pdf_header).toBe('%PDF');
        expect(surat.template_contains_nomor).toBe(true);
        expect(surat.pdf_contains_kode).toBe(true);
    });

    test('dua setujui berurutan menghasilkan nomor unik berurutan dalam tahun yang sama', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.nomor.seq.${stamp}@example.com`;
        const wargaEmail = `warga.nomor.seq.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 2);
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 3);
        const namaSurat = `Surat Keterangan Usaha Nomor Seq E2E ${stamp}`;
        const nomorPengajuan1 = `PJ-${String(stamp).slice(-8)}-7302`;
        const nomorPengajuan2 = `PJ-${String(stamp).slice(-8)}-7303`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Nomor Seq',
            role: 'admin',
            nik: adminNik,
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Nomor Seq',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);
        const pengajuanId1 = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: `Keperluan nomor seq 1 ${stamp}`,
            nomorPengajuan: nomorPengajuan1,
        });
        const pengajuanId2 = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: `Keperluan nomor seq 2 ${stamp}`,
            nomorPengajuan: nomorPengajuan2,
        });

        await loginAs(page, adminEmail);

        await page.goto(`/admin/verifikasi/${pengajuanId1}`);
        await page.locator('[data-test="verifikasi-detail-setujui-button"]').click();
        await expect(page).toHaveURL(/\/admin\/verifikasi$/);

        await page.goto(`/admin/verifikasi/${pengajuanId2}`);
        await page.locator('[data-test="verifikasi-detail-setujui-button"]').click();
        await expect(page).toHaveURL(/\/admin\/verifikasi$/);

        const surat1 = getSuratNomorPayload(pengajuanId1);
        const surat2 = getSuratNomorPayload(pengajuanId2);

        expect(surat1.exists).toBe(true);
        expect(surat2.exists).toBe(true);
        expect(surat1.nomor_surat).not.toBe(surat2.nomor_surat);

        const urut1 = Number((surat1.nomor_surat ?? '').split('/')[1]);
        const urut2 = Number((surat2.nomor_surat ?? '').split('/')[1]);
        expect(urut2).toBe(urut1 + 1);
        expect(surat1.nomor_surat).not.toBe(nomorPengajuan1);
        expect(surat2.nomor_surat).not.toBe(nomorPengajuan2);
    });

    test('tolak tidak mengalokasikan nomor surat resmi', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.nomor.tolak.${stamp}@example.com`;
        const wargaEmail = `warga.nomor.tolak.${stamp}@example.com`;
        const adminNik = uniqueNik(Number(String(stamp).slice(-6)) + 4);
        const wargaNik = uniqueNik(Number(String(stamp).slice(-6)) + 5);
        const namaSurat = `Surat Keterangan Usaha Nomor Tolak E2E ${stamp}`;
        const nomorPengajuan = `PJ-${String(stamp).slice(-8)}-7304`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Nomor Tolak',
            role: 'admin',
            nik: adminNik,
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Nomor Tolak',
            role: 'warga',
            nik: wargaNik,
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        ensureJenisSurat(namaSurat);
        const jenisSuratId = getJenisSuratIdByName(namaSurat);
        const pengajuanId = ensurePengajuanDiajukan({
            userId: wargaId,
            jenisSuratId,
            keperluan: `Keperluan nomor tolak ${stamp}`,
            nomorPengajuan,
        });

        await loginAs(page, adminEmail);
        await page.goto(`/admin/verifikasi/${pengajuanId}`);
        await page.locator('[data-test="verifikasi-detail-tolak-button"]').click();
        await page.locator('[data-test="verifikasi-detail-catatan-admin"]').fill(
            'Dokumen tidak lengkap sehingga nomor surat tidak diterbitkan',
        );
        await page.locator('[data-test="verifikasi-detail-tolak-confirm"]').click();
        await expect(page).toHaveURL(/\/admin\/verifikasi$/);

        const surat = getSuratNomorPayload(pengajuanId);
        expect(surat.exists).toBe(false);
        expect(surat.nomor_surat).toBeNull();
        expect(surat.file_exists).toBe(false);
    });
});
