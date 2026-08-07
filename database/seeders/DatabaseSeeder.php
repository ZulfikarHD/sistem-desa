<?php

namespace Database\Seeders;

use App\Models\DokumenPersyaratan;
use App\Models\JenisSurat;
use App\Models\LogVerifikasi;
use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            JenisSuratSeeder::class,
        ]);

        // ---------------------------------------------------------------------------
        // DEMO / FACTORY DATA — disable or comment this out in production.
        // Hanya untuk testing & development lokal (isi dashboard, verifikasi, rekap).
        // ---------------------------------------------------------------------------
        $this->seedDemoFactoryData();
    }

    /**
     * Data contoh lewat factory agar UI punya konten realistis.
     * Matikan pemanggilan method ini di production.
     */
    private function seedDemoFactoryData(): void
    {
        $admin = User::query()->where('email', 'admin@desa.test')->firstOrFail();
        $warga = User::query()->where('email', 'warga@desa.test')->firstOrFail();
        $jenisSurat = JenisSurat::query()->get();

        // Pengajuan menunggu verifikasi
        PengajuanSurat::factory()
            ->count(5)
            ->recycle([$warga])
            ->recycle($jenisSurat)
            ->create(['user_id' => $warga->id]);

        // Pengajuan ditolak
        $ditolak = PengajuanSurat::factory()
            ->count(2)
            ->ditolak('Dokumen kurang jelas / tidak lengkap.')
            ->recycle([$admin, $warga])
            ->recycle($jenisSurat)
            ->create([
                'user_id' => $warga->id,
                'diverifikasi_oleh' => $admin->id,
            ]);

        foreach ($ditolak as $pengajuan) {
            DokumenPersyaratan::factory()->ktp()->create(['pengajuan_id' => $pengajuan->id]);
            DokumenPersyaratan::factory()->kk()->create(['pengajuan_id' => $pengajuan->id]);
            LogVerifikasi::factory()->tolak($pengajuan->catatan_admin)->create([
                'pengajuan_id' => $pengajuan->id,
                'admin_id' => $admin->id,
            ]);
            Notifikasi::factory()->create([
                'user_id' => $warga->id,
                'pengajuan_id' => $pengajuan->id,
                'pesan' => 'Pengajuan '.$pengajuan->nomor_pengajuan.' ditolak: '.$pengajuan->catatan_admin,
            ]);
        }

        // Pengajuan diproses (+ surat terbit)
        $diproses = PengajuanSurat::factory()
            ->count(3)
            ->diproses()
            ->recycle([$admin, $warga])
            ->recycle($jenisSurat)
            ->create([
                'user_id' => $warga->id,
                'diverifikasi_oleh' => $admin->id,
            ]);

        foreach ($diproses as $pengajuan) {
            $this->attachDokumenDanLogSetujui($pengajuan, $admin, $warga);
            // Terbitkan PDF nyata (bukan path palsu factory) agar unduh demo tidak 404.
            SuratTerbit::terbitkanUntuk($pengajuan->fresh(['user', 'jenisSurat']), $admin->id);
        }

        // Pengajuan siap diambil
        $siapDiambil = PengajuanSurat::factory()
            ->count(2)
            ->siapDiambil()
            ->recycle([$admin, $warga])
            ->recycle($jenisSurat)
            ->create([
                'user_id' => $warga->id,
                'diverifikasi_oleh' => $admin->id,
            ]);

        foreach ($siapDiambil as $pengajuan) {
            $this->attachDokumenDanLogSetujui($pengajuan, $admin, $warga);
            $surat = SuratTerbit::terbitkanUntuk($pengajuan->fresh(['user', 'jenisSurat']), $admin->id);
            $surat->update([
                'tanggal_pengambilan' => now()->addWeekdays(2)->toDateString(),
                'siap_diambil_at' => now(),
                'jam_kerja_label' => 'Senin–Kamis 08.00–16.00 WIB',
            ]);
            Notifikasi::factory()->create([
                'user_id' => $warga->id,
                'pengajuan_id' => $pengajuan->id,
                'pesan' => 'Surat Anda ('.$pengajuan->nomor_pengajuan.') sudah siap diambil.',
            ]);
        }

        // Pengajuan selesai (QR sudah dipakai)
        $selesai = PengajuanSurat::factory()
            ->count(2)
            ->selesai()
            ->recycle([$admin, $warga])
            ->recycle($jenisSurat)
            ->create([
                'user_id' => $warga->id,
                'diverifikasi_oleh' => $admin->id,
            ]);

        foreach ($selesai as $pengajuan) {
            $this->attachDokumenDanLogSetujui($pengajuan, $admin, $warga);
            $surat = SuratTerbit::terbitkanUntuk($pengajuan->fresh(['user', 'jenisSurat']), $admin->id);
            $surat->update([
                'qr_status' => SuratTerbit::QR_STATUS_INVALID,
                'qr_digunakan_at' => now(),
                'qr_digunakan_oleh' => $admin->id,
                'tanggal_pengambilan' => now()->subDays(3)->toDateString(),
                'siap_diambil_at' => now()->subDays(3),
                'jam_kerja_label' => 'Senin–Kamis 08.00–16.00 WIB',
            ]);
        }
    }

    private function attachDokumenDanLogSetujui(PengajuanSurat $pengajuan, User $admin, User $warga): void
    {
        DokumenPersyaratan::factory()->ktp()->create(['pengajuan_id' => $pengajuan->id]);
        DokumenPersyaratan::factory()->kk()->create(['pengajuan_id' => $pengajuan->id]);
        LogVerifikasi::factory()->setujui()->create([
            'pengajuan_id' => $pengajuan->id,
            'admin_id' => $admin->id,
        ]);
        Notifikasi::factory()->create([
            'user_id' => $warga->id,
            'pengajuan_id' => $pengajuan->id,
            'pesan' => 'Pengajuan '.$pengajuan->nomor_pengajuan.' telah diproses.',
        ]);
    }
}
