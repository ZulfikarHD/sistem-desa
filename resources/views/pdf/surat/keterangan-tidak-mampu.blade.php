@extends('pdf.surat.layout')

@section('body')
    <div class="judul">
        <h1>Surat Keterangan Tidak Mampu</h1>
    </div>
    <div class="nomor">Nomor: {{ $nomorSurat }}</div>

    <p class="isi">
        Yang bertanda tangan di bawah ini, {{ $desa['penandatangan_jabatan'] }} {{ $desa['nama_desa'] }},
        Kecamatan {{ $desa['kecamatan'] }}, {{ $desa['kabupaten'] }}, menerangkan dengan sesungguhnya bahwa:
    </p>

    <table class="data">
        <tr>
            <td class="label">Nama</td>
            <td class="sep">:</td>
            <td>{{ $pemohon?->name }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td class="sep">:</td>
            <td>{{ $pemohon?->nik }}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td class="sep">:</td>
            <td>{{ $pemohon?->alamat }}</td>
        </tr>
        <tr>
            <td class="label">Keperluan</td>
            <td class="sep">:</td>
            <td>{{ $pengajuan->keperluan }}</td>
        </tr>
    </table>

    <p class="penutup">
        Orang tersebut di atas benar termasuk keluarga kurang mampu di wilayah {{ $desa['nama_desa'] }}.
        Surat keterangan ini dibuat untuk keperluan tersebut di atas.
    </p>
@endsection
