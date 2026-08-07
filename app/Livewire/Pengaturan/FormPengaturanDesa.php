<?php

namespace App\Livewire\Pengaturan;

use App\Models\PengaturanDesa;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pengaturan Desa')]
class FormPengaturanDesa extends Component
{
    public string $nama_desa = '';

    public string $kecamatan = '';

    public string $kabupaten = '';

    public string $provinsi = '';

    public string $alamat_kantor = '';

    public string $kode_pos = '';

    public string $telepon = '';

    public string $penandatangan_nama = '';

    public string $penandatangan_jabatan = '';

    public string $kode_klasifikasi = '';

    public string $kode_desa = '';

    /**
     * Muat baris tunggal pengaturan (buat dari config jika belum ada).
     */
    public function mount(): void
    {
        $row = PengaturanDesa::instance();

        $this->nama_desa = $row->nama_desa;
        $this->kecamatan = $row->kecamatan;
        $this->kabupaten = $row->kabupaten;
        $this->provinsi = $row->provinsi;
        $this->alamat_kantor = $row->alamat_kantor;
        $this->kode_pos = (string) ($row->kode_pos ?? '');
        $this->telepon = (string) ($row->telepon ?? '');
        $this->penandatangan_nama = $row->penandatangan_nama;
        $this->penandatangan_jabatan = $row->penandatangan_jabatan;
        $this->kode_klasifikasi = $row->kode_klasifikasi;
        $this->kode_desa = $row->kode_desa;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama_desa' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kabupaten' => ['required', 'string', 'max:100'],
            'provinsi' => ['required', 'string', 'max:100'],
            'alamat_kantor' => ['required', 'string', 'max:255'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'penandatangan_nama' => ['required', 'string', 'max:100'],
            'penandatangan_jabatan' => ['required', 'string', 'max:100'],
            'kode_klasifikasi' => ['required', 'string', 'max:20'],
            'kode_desa' => ['required', 'string', 'max:30'],
        ];
    }

    /**
     * Simpan identitas desa ke baris tunggal.
     */
    public function simpan(): void
    {
        $validated = $this->validate();

        $row = PengaturanDesa::instance();
        $row->update([
            'nama_desa' => $validated['nama_desa'],
            'kecamatan' => $validated['kecamatan'],
            'kabupaten' => $validated['kabupaten'],
            'provinsi' => $validated['provinsi'],
            'alamat_kantor' => $validated['alamat_kantor'],
            'kode_pos' => $validated['kode_pos'] !== '' ? $validated['kode_pos'] : null,
            'telepon' => $validated['telepon'] !== '' ? $validated['telepon'] : null,
            'penandatangan_nama' => $validated['penandatangan_nama'],
            'penandatangan_jabatan' => $validated['penandatangan_jabatan'],
            'kode_klasifikasi' => $validated['kode_klasifikasi'],
            'kode_desa' => $validated['kode_desa'],
        ]);

        Flux::toast(variant: 'success', text: 'Pengaturan desa berhasil disimpan.');
    }

    public function render(): View
    {
        return view('livewire.pengaturan.form-pengaturan-desa');
    }
}
