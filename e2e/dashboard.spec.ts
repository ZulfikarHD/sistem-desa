import { test, expect } from '@playwright/test';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * US-8.1 — Dashboard Admin (aging cards + urgent queue)
 * US-8.2 — Dashboard Warga (hero status + riwayat + notifikasi)
 */

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

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
        `'alamat' => 'Jl. E2E Dashboard No. 1',`,
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

function ensureJenisSurat(namaSurat: string): number {
    runTinker(
        [
            `\\App\\Models\\JenisSurat::updateOrCreate(`,
            `['nama_surat' => ${JSON.stringify(namaSurat)}],`,
            `[`,
            `'deskripsi' => 'Deskripsi e2e dashboard',`,
            `'persyaratan_dokumen' => "- Fotokopi KTP\\n- Fotokopi KK",`,
            `]`,
            `);`,
        ].join(''),
    );

    const id = Number(
        runTinker(`echo \\App\\Models\\JenisSurat::where('nama_surat', ${JSON.stringify(namaSurat)})->value('id');`).trim(),
    );
    if (!id) {
        throw new Error(`Failed to resolve jenis surat id for ${namaSurat}`);
    }

    return id;
}

async function loginAs(page: import('@playwright/test').Page, email: string): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill('password');
    await page.locator('[data-test="login-button"]').click();
}

test.describe('US-8.1 Dashboard Admin', () => {
    test('admin melihat kartu aging, severity urgent, dan seksi perlu ditindaklanjuti', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.dash.${stamp}@example.com`;
        const wargaEmail = `warga.dash.admin.${stamp}@example.com`;
        const nomor = `PJ-DASH-A-${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Dashboard E2E',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Aging Card E2E',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-5) + '1')),
        });

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = ensureJenisSurat('Surat Keterangan Aging Card E2E');

        const pengajuanId = Number(
            runTinker(
                [
                    `$p = \\App\\Models\\PengajuanSurat::create([`,
                    `'user_id' => ${wargaId},`,
                    `'jenis_surat_id' => ${jenisId},`,
                    `'nomor_pengajuan' => ${JSON.stringify(nomor)},`,
                    `'keperluan' => 'E2E dashboard admin aging',`,
                    `'status' => \\App\\Models\\PengajuanSurat::STATUS_DIAJUKAN,`,
                    `'tanggal_pengajuan' => now('Asia/Jakarta')->subDays(3650)->toDateString(),`,
                    `]);`,
                    `$p->created_at = now('Asia/Jakarta')->subDays(3650);`,
                    `$p->updated_at = now('Asia/Jakarta')->subDays(3650);`,
                    `$p->save();`,
                    `echo $p->id;`,
                ].join(''),
            ).trim(),
        );

        await loginAs(page, adminEmail);
        await expect(page).toHaveURL(/\/admin\/dashboard$/);
        await expect(page.locator('[data-test="dashboard-admin-heading"]')).toBeVisible();

        const card = page.locator('[data-test="dashboard-admin-card-diajukan"]');
        await expect(card).toBeVisible();
        await expect(card).toHaveAttribute('data-severity', 'urgent');
        await expect(page.locator('[data-test="dashboard-admin-card-diajukan-sub"]')).toContainText('tertunda > 3 hari');

        // Tabel aktif selalu memuat item (tidak terbatas prioritas mendesak top-5)
        await expect(page.locator(`[data-test="dashboard-admin-aktif-row-${pengajuanId}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="dashboard-admin-detail-${pengajuanId}"]`)).toBeVisible();
        await expect(page.getByText(nomor).first()).toBeVisible();
    });

    test('kartu diajukan mengarahkan ke daftar pengajuan surat', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.dash.nav.${stamp}@example.com`;

        ensureUser({
            email: adminEmail,
            name: 'Admin Dashboard Nav',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });

        await loginAs(page, adminEmail);
        await page.locator('[data-test="dashboard-admin-card-diajukan"]').click();
        await expect(page).toHaveURL(/\/admin\/verifikasi/);
        await expect(page.getByText(/Daftar Pengajuan Surat/i).first()).toBeVisible();
    });

    test('warga mendapat 403 saat membuka dashboard admin', async ({ page }) => {
        const stamp = Date.now();
        const wargaEmail = `warga.dash.forbid.${stamp}@example.com`;

        ensureUser({
            email: wargaEmail,
            name: 'Warga Forbid Admin Dash',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });

        await loginAs(page, wargaEmail);
        await page.goto('/admin/dashboard');
        await expect(page.getByText(/403|Forbidden|Tidak diizinkan|unauthorized/i).first()).toBeVisible();
    });
});

test.describe('US-8.2 Dashboard Warga', () => {
    test('hero menampilkan status aktif dan penjelasan untuk diproses tanpa unduh', async ({ page }) => {
        const stamp = Date.now();
        const adminEmail = `admin.dash.warga.${stamp}@example.com`;
        const wargaEmail = `warga.dash.hero.${stamp}@example.com`;
        const nomor = `PJ-DASH-W-${stamp}`;

        ensureUser({
            email: adminEmail,
            name: 'Admin For Warga Dash',
            role: 'admin',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });
        ensureUser({
            email: wargaEmail,
            name: 'Warga Dashboard Hero',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-5) + '2')),
        });

        const adminId = getUserIdByEmail(adminEmail);
        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = ensureJenisSurat('Surat Keterangan Dashboard Warga E2E');

        const pengajuanId = Number(
            runTinker(
                [
                    `$p = \\App\\Models\\PengajuanSurat::create([`,
                    `'user_id' => ${wargaId},`,
                    `'jenis_surat_id' => ${jenisId},`,
                    `'nomor_pengajuan' => ${JSON.stringify(nomor)},`,
                    `'keperluan' => 'E2E dashboard warga hero',`,
                    `'status' => \\App\\Models\\PengajuanSurat::STATUS_DIPROSES,`,
                    `'diverifikasi_oleh' => ${adminId},`,
                    `'tanggal_pengajuan' => now('Asia/Jakarta')->toDateString(),`,
                    `]);`,
                    `\\App\\Models\\SuratTerbit::create([`,
                    `'pengajuan_id' => $p->id,`,
                    `'nomor_surat' => ${JSON.stringify(`470/${stamp}/DS-WDN/VIII/2026`)},`,
                    `'file_path' => 'surat-terbit/'.$p->id.'/surat.pdf',`,
                    `'tanggal_terbit' => now('Asia/Jakarta')->toDateString(),`,
                    `'qr_token' => \\Illuminate\\Support\\Str::random(64),`,
                    `'qr_status' => \\App\\Models\\SuratTerbit::QR_STATUS_VALID,`,
                    `'diterbitkan_oleh' => ${adminId},`,
                    `]);`,
                    `\\Illuminate\\Support\\Facades\\Storage::disk('local')->put('surat-terbit/'.$p->id.'/surat.pdf', '%PDF-1.4 e2e');`,
                    `echo $p->id;`,
                ].join(''),
            ).trim(),
        );

        expect(pengajuanId).toBeGreaterThan(0);

        await loginAs(page, wargaEmail);
        await expect(page).toHaveURL(/\/dashboard$/);
        await expect(page.locator('[data-test="dashboard-warga-heading"]')).toBeVisible();
        await expect(page.getByRole('heading', { name: /Status surat Anda/i })).toBeVisible();
        await expect(page.locator(`[data-test="dashboard-warga-hero-card-${pengajuanId}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="dashboard-warga-hero-alur-${pengajuanId}"]`)).toBeVisible();
        await expect(page.locator(`[data-test="dashboard-warga-hero-penjelasan-${pengajuanId}"]`)).toContainText(
            'sedang disiapkan',
        );
        await expect(page.locator(`[data-test="dashboard-warga-unduh-${pengajuanId}"]`)).toHaveCount(0);
        await expect(page.locator('[data-test="dashboard-warga-ajukan-baru"]')).toBeVisible();
        await expect(page.locator('[data-test="dashboard-warga-riwayat-section"]')).toBeVisible();
    });

    test('tanpa pengajuan aktif menampilkan CTA Ajukan Surat Sekarang', async ({ page }) => {
        const stamp = Date.now();
        const wargaEmail = `warga.dash.empty.${stamp}@example.com`;

        ensureUser({
            email: wargaEmail,
            name: 'Warga Dashboard Empty',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });

        await loginAs(page, wargaEmail);
        await expect(page.getByRole('heading', { name: /Mulai pengajuan surat Anda/i })).toBeVisible();
        await expect(page.locator('[data-test="dashboard-warga-hero-empty"]')).toBeVisible();
        await expect(page.locator('[data-test="dashboard-warga-cta-ajukan"]')).toBeVisible();
        await expect(page.locator('[data-test="dashboard-warga-ajukan-baru"]')).toHaveCount(0);
    });

    test('banner notifikasi muncul saat ada notifikasi belum dibaca', async ({ page }) => {
        const stamp = Date.now();
        const wargaEmail = `warga.dash.notif.${stamp}@example.com`;
        const nomor = `PJ-DASH-N-${stamp}`;

        ensureUser({
            email: wargaEmail,
            name: 'Warga Dashboard Notif',
            role: 'warga',
            nik: uniqueNik(Number(String(stamp).slice(-6))),
        });

        const wargaId = getUserIdByEmail(wargaEmail);
        const jenisId = ensureJenisSurat('Surat Keterangan Dashboard Notif E2E');

        runTinker(
            [
                `$p = \\App\\Models\\PengajuanSurat::create([`,
                `'user_id' => ${wargaId},`,
                `'jenis_surat_id' => ${jenisId},`,
                `'nomor_pengajuan' => ${JSON.stringify(nomor)},`,
                `'keperluan' => 'E2E dashboard notif',`,
                `'status' => \\App\\Models\\PengajuanSurat::STATUS_DIAJUKAN,`,
                `'tanggal_pengajuan' => now('Asia/Jakarta')->toDateString(),`,
                `]);`,
                `\\App\\Models\\Notifikasi::create([`,
                `'user_id' => ${wargaId},`,
                `'pengajuan_id' => $p->id,`,
                `'pesan' => 'Notifikasi e2e dashboard warga belum dibaca',`,
                `'status_baca' => \\App\\Models\\Notifikasi::STATUS_BELUM,`,
                `'created_at' => now(),`,
                `]);`,
            ].join(''),
        );

        await loginAs(page, wargaEmail);
        await expect(page.locator('[data-test="dashboard-warga-notif-banner"]')).toBeVisible();
        await expect(page.locator('[data-test="dashboard-warga-notif-banner"]')).toContainText('notifikasi baru');
        await expect(
            page.locator('[data-test="dashboard-warga-notif-list"]').getByText('Notifikasi e2e dashboard warga belum dibaca'),
        ).toBeVisible();
    });
});
