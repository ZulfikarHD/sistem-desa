@extends('pdf.surat.layout')

@section('body')
    <div class="judul">
        <h1>Bukti Pengambilan Berkas</h1>
    </div>
    <div class="nomor">Referensi: {{ $nomorSurat }}</div>

    <p class="isi">
        Dokumen ini adalah bukti untuk pengambilan berkas surat keterangan di kantor desa.
        Bukan surat keterangan resmi. Tunjukkan kode QR di bawah kepada petugas saat pengambilan.
    </p>

    <table class="data">
        <tr>
            <td class="label">Nomor Pengajuan</td>
            <td class="sep">:</td>
            <td>{{ $pengajuan->nomor_pengajuan }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Surat</td>
            <td class="sep">:</td>
            <td>{{ $jenisSurat?->nama_surat ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Pemohon</td>
            <td class="sep">:</td>
            <td>{{ $pemohon?->name }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td class="sep">:</td>
            <td>{{ $pemohon?->nik }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Pengambilan</td>
            <td class="sep">:</td>
            <td>
                @if ($tanggalPengambilan)
                    {{ $tanggalPengambilan->copy()->locale('id')->translatedFormat('d F Y') }}
                @else
                    Belum ditetapkan
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Jam Kerja</td>
            <td class="sep">:</td>
            <td>{{ $jamKerjaLabel ?: 'Belum ditetapkan' }}</td>
        </tr>
    </table>

    <p class="penutup">
        Datang ke kantor {{ $desa['nama_desa'] }} pada jadwal di atas dengan membawa bukti ini.
        Kode QR hanya berlaku sekali saat petugas memindai pengambilan.
    </p>

    <div class="qr">
        <img src="{{ $qrDataUri }}" alt="QR Code">
        <div class="qr-label">Scan QR saat pengambilan (sekali pakai)</div>
    </div>
@endsection
