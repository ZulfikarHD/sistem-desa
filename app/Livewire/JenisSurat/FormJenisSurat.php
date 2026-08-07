<?php

namespace App\Livewire\JenisSurat;

use App\Models\JenisSurat;
use App\Models\JenisSuratPersyaratan;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Halaman khusus tambah / ubah jenis surat (bukan modal).
 */
class FormJenisSurat extends Component
{
    /** ID jenis surat yang diubah; null = mode tambah. */
    public ?int $editingId = null;

    public string $nama_surat = '';

    public string $deskripsi = '';

    /**
     * Baris persyaratan di form (belum tersimpan).
     *
     * @var list<array{key: string, nama: string, cara_pemenuhan: string, is_wajib: bool|int}>
     */
    public array $persyaratanRows = [];

    /**
     * Mode tambah: baris kosong default. Mode ubah: muat data aktif (bukan arsip).
     */
    public function mount(?JenisSurat $jenisSurat = null): void
    {
        if ($jenisSurat === null) {
            $this->persyaratanRows = [$this->emptyPersyaratanRow()];

            return;
        }

        $jenisSurat->load('persyaratan');

        $this->editingId = $jenisSurat->id;
        $this->nama_surat = $jenisSurat->nama_surat;
        $this->deskripsi = $jenisSurat->deskripsi ?? '';
        $this->persyaratanRows = $jenisSurat->persyaratan
            ->map(fn (JenisSuratPersyaratan $row): array => [
                'key' => 'db-'.$row->id,
                'nama' => $row->nama,
                'cara_pemenuhan' => $row->cara_pemenuhan,
                // Simpan sebagai 1/0 agar cocok dengan flux:radio value string.
                'is_wajib' => $row->cara_pemenuhan === JenisSuratPersyaratan::CARA_UNGGAH
                    ? ($row->is_wajib ? 1 : 0)
                    : 1,
            ])
            ->values()
            ->all();

        if ($this->persyaratanRows === []) {
            $this->persyaratanRows = [$this->emptyPersyaratanRow()];
        }
    }

    /**
     * Saat cara memenuhi berubah: reset is_wajib jika bukan unggah; default wajib jika unggah.
     */
    public function updatedPersyaratanRows(mixed $value, ?string $key): void
    {
        if ($key === null || ! str_ends_with($key, 'cara_pemenuhan')) {
            return;
        }

        $parts = explode('.', $key);
        $index = isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : null;

        if ($index === null || ! isset($this->persyaratanRows[$index])) {
            return;
        }

        $cara = $this->persyaratanRows[$index]['cara_pemenuhan'] ?? '';

        if ($cara !== JenisSuratPersyaratan::CARA_UNGGAH) {
            $this->persyaratanRows[$index]['is_wajib'] = 1;

            return;
        }

        // Default aman: baris unggah baru / ganti ke unggah → Wajib.
        if (! array_key_exists('is_wajib', $this->persyaratanRows[$index])) {
            $this->persyaratanRows[$index]['is_wajib'] = 1;
        }
    }

    /**
     * Tambah baris persyaratan kosong di form.
     */
    public function addPersyaratanRow(): void
    {
        $this->persyaratanRows[] = $this->emptyPersyaratanRow();
    }

    /**
     * Hapus baris persyaratan di indeks tertentu.
     */
    public function removePersyaratanRow(int $index): void
    {
        if (! isset($this->persyaratanRows[$index])) {
            return;
        }

        unset($this->persyaratanRows[$index]);
        $this->persyaratanRows = array_values($this->persyaratanRows);
    }

    /**
     * Naikkan urutan baris persyaratan.
     */
    public function movePersyaratanRowUp(int $index): void
    {
        if ($index <= 0 || ! isset($this->persyaratanRows[$index])) {
            return;
        }

        $rows = $this->persyaratanRows;
        [$rows[$index - 1], $rows[$index]] = [$rows[$index], $rows[$index - 1]];
        $this->persyaratanRows = array_values($rows);
    }

    /**
     * Turunkan urutan baris persyaratan.
     */
    public function movePersyaratanRowDown(int $index): void
    {
        if (! isset($this->persyaratanRows[$index]) || $index >= count($this->persyaratanRows) - 1) {
            return;
        }

        $rows = $this->persyaratanRows;
        [$rows[$index + 1], $rows[$index]] = [$rows[$index], $rows[$index + 1]];
        $this->persyaratanRows = array_values($rows);
    }

    /**
     * Template cepat Domisili-style: KTP + KK (unggah wajib) + Pengantar RT/RW (bawa kantor).
     */
    public function applyDomisiliTemplate(): void
    {
        $this->persyaratanRows = [
            [
                'key' => (string) Str::uuid(),
                'nama' => 'Fotokopi KTP',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                'is_wajib' => 1,
            ],
            [
                'key' => (string) Str::uuid(),
                'nama' => 'Fotokopi Kartu Keluarga (KK)',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                'is_wajib' => 1,
            ],
            [
                'key' => (string) Str::uuid(),
                'nama' => 'Surat pengantar RT/RW',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                'is_wajib' => 1,
            ],
        ];
    }

    /**
     * Simpan data baru atau pembaruan jenis surat beserta baris persyaratan.
     */
    public function save(): void
    {
        $this->normalizePersyaratanRowsBeforeValidate();

        $validated = $this->validate($this->rules());

        $payload = [
            'nama_surat' => $validated['nama_surat'],
            'deskripsi' => $validated['deskripsi'] !== '' ? $validated['deskripsi'] : null,
        ];

        $rows = collect($validated['persyaratanRows'])
            ->values()
            ->map(fn (array $row, int $index): array => [
                'nama' => trim($row['nama']),
                'cara_pemenuhan' => $row['cara_pemenuhan'],
                'is_wajib' => $row['cara_pemenuhan'] === JenisSuratPersyaratan::CARA_UNGGAH
                    ? filter_var($row['is_wajib'], FILTER_VALIDATE_BOOLEAN)
                    : true,
                'urutan' => $index,
            ])
            ->all();

        if ($this->editingId === null) {
            $jenisSurat = JenisSurat::query()->create([
                ...$payload,
                'persyaratan_dokumen' => JenisSuratPersyaratan::generateRingkasan($rows),
            ]);
            $jenisSurat->syncPersyaratan($rows);
            Flux::toast(variant: 'success', text: 'Jenis surat berhasil ditambahkan.');
        } else {
            $jenisSurat = JenisSurat::query()->findOrFail($this->editingId);
            $jenisSurat->update($payload);
            $jenisSurat->syncPersyaratan($rows);
            Flux::toast(variant: 'success', text: 'Jenis surat berhasil diperbarui.');
        }

        $this->redirect(route('jenis-surat.index'), navigate: true);
    }

    /**
     * Kembali ke daftar tanpa menyimpan.
     */
    public function cancel(): void
    {
        $this->redirect(route('jenis-surat.index'), navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'nama_surat' => [
                'required',
                'string',
                'max:100',
                Rule::unique('jenis_surat', 'nama_surat')->ignore($this->editingId),
            ],
            'deskripsi' => ['nullable', 'string'],
            'persyaratanRows' => ['required', 'array', 'min:1'],
            'persyaratanRows.*.nama' => ['required', 'string', 'max:255'],
            'persyaratanRows.*.cara_pemenuhan' => [
                'required',
                'string',
                Rule::in(array_keys(JenisSuratPersyaratan::caraPemenuhanOptions())),
            ],
            'persyaratanRows.*.is_wajib' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'nama_surat.required' => 'Nama surat wajib diisi.',
            'nama_surat.unique' => 'Nama surat sudah digunakan.',
            'nama_surat.max' => 'Nama surat maksimal 100 karakter.',
            'persyaratanRows.required' => 'Minimal satu persyaratan harus diisi.',
            'persyaratanRows.min' => 'Minimal satu persyaratan harus diisi.',
            'persyaratanRows.*.nama.required' => 'Nama syarat wajib diisi.',
            'persyaratanRows.*.nama.max' => 'Nama syarat maksimal 255 karakter.',
            'persyaratanRows.*.cara_pemenuhan.required' => 'Cara memenuhi wajib dipilih.',
            'persyaratanRows.*.cara_pemenuhan.in' => 'Cara memenuhi tidak valid.',
            'persyaratanRows.*.is_wajib.required' => 'Pilihan wajib/boleh dikosongkan harus ditentukan.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'persyaratanRows.*.nama' => 'nama syarat',
            'persyaratanRows.*.cara_pemenuhan' => 'cara memenuhi',
            'persyaratanRows.*.is_wajib' => 'kewajiban unggah',
        ];
    }

    public function render(): View
    {
        $title = $this->editingId === null ? 'Tambah Jenis Surat' : 'Ubah Jenis Surat';

        return view('livewire.jenis-surat.form-jenis-surat', [
            'caraPemenuhanOptions' => JenisSuratPersyaratan::caraPemenuhanOptions(),
            'caraPemenuhanHelpers' => JenisSuratPersyaratan::caraPemenuhanHelpers(),
        ])->title($title);
    }

    /**
     * @return array{key: string, nama: string, cara_pemenuhan: string, is_wajib: int}
     */
    protected function emptyPersyaratanRow(): array
    {
        return [
            'key' => (string) Str::uuid(),
            'nama' => '',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
            'is_wajib' => 1,
        ];
    }

    /**
     * Pastikan setiap baris punya key & is_wajib konsisten sebelum validasi.
     */
    protected function normalizePersyaratanRowsBeforeValidate(): void
    {
        foreach ($this->persyaratanRows as $index => $row) {
            if (! isset($row['key']) || $row['key'] === '') {
                $this->persyaratanRows[$index]['key'] = (string) Str::uuid();
            }

            $cara = $row['cara_pemenuhan'] ?? JenisSuratPersyaratan::CARA_UNGGAH;
            $this->persyaratanRows[$index]['cara_pemenuhan'] = $cara;

            if ($cara !== JenisSuratPersyaratan::CARA_UNGGAH) {
                $this->persyaratanRows[$index]['is_wajib'] = 1;
            } else {
                $this->persyaratanRows[$index]['is_wajib'] = filter_var($row['is_wajib'] ?? true, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            }
        }
    }
}
