{{-- Layout kop kantor untuk bukti Pengambilan Berkas (bukan surat keterangan resmi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $nomorSurat }}</title>
    <style>
        @page { margin: 2cm 2.2cm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            color: #111;
            line-height: 1.45;
        }
        .kop { text-align: center; border-bottom: 3px double #111; padding-bottom: 8px; margin-bottom: 18px; }
        .kop .pemerintah { font-size: 12pt; font-weight: bold; text-transform: uppercase; margin: 0; }
        .kop .desa { font-size: 16pt; font-weight: bold; text-transform: uppercase; margin: 2px 0; }
        .kop .alamat { font-size: 9pt; margin: 0; }
        .judul { text-align: center; margin: 18px 0 6px; }
        .judul h1 { font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 0; letter-spacing: 1px; }
        .nomor { text-align: center; margin-bottom: 16px; font-size: 11pt; }
        .isi { text-align: justify; margin-bottom: 10px; }
        .data { margin: 12px 0 12px 24px; }
        .data td { padding: 2px 6px; vertical-align: top; }
        .data td.label { width: 160px; }
        .data td.sep { width: 12px; }
        .penutup { text-align: justify; margin-top: 14px; }
        .qr { margin-top: 28px; text-align: center; }
        .qr img { width: 110px; height: 110px; }
        .qr-label { font-size: 9pt; color: #333; margin-top: 6px; }
    </style>
</head>
<body>
    <div class="kop">
        <p class="pemerintah">Pemerintah {{ $desa['kabupaten'] }}</p>
        <p class="pemerintah">Kecamatan {{ $desa['kecamatan'] }}</p>
        <p class="desa">{{ $desa['nama_desa'] }}</p>
        <p class="alamat">{{ $desa['alamat_kantor'] }}{{ ! empty($desa['kode_pos']) ? ', '.$desa['kode_pos'] : '' }}
            @if(! empty($desa['telepon']))
                &nbsp;|&nbsp; Telp. {{ $desa['telepon'] }}
            @endif
        </p>
    </div>

    @yield('body')
</body>
</html>
