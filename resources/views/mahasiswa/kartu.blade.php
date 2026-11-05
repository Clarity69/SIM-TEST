<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Mahasiswa</title>
    <style>
        @page { margin: {{ $tunggal ? '0' : '12mm 15mm' }}; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; color: #0f172a; }

        .lembar { width: 100%; border-collapse: separate; border-spacing: 6mm 5mm; margin: -5mm -6mm; }
        .lembar td.sel { width: 85.6mm; height: 54mm; padding: 0; vertical-align: top; }

        .kartu { position: relative; width: 85.6mm; height: 53.8mm; overflow: hidden; background: #ffffff; }
        .lembar .kartu { border: 0.2mm dashed #94a3b8; }

        .kepala { position: absolute; left: 0; top: 0; width: 85.6mm; height: 13mm; background: #0a294b; }
        .kepala .aksen { position: absolute; left: 0; bottom: 0; width: 85.6mm; height: 0.9mm; background: #fe6500; }
        .kepala img { position: absolute; left: 3.5mm; top: 2mm; width: 8.5mm; height: 8.5mm; }
        .kepala .judul { position: absolute; left: 14mm; top: 2.1mm; color: #ffffff; }
        .kepala .judul .kecil { font-size: 4.6pt; letter-spacing: 0.6pt; color: #9fd1f7; text-transform: uppercase; }
        .kepala .judul .besar { font-size: 7.4pt; font-weight: bold; margin-top: 0.4mm; }
        .kepala .judul .kampus { font-size: 5pt; color: #d6ebfb; margin-top: 0.3mm; }

        .foto { position: absolute; left: 3.5mm; top: 16.5mm; width: 19mm; height: 24mm; border-radius: 1.5mm; overflow: hidden; background: #e7f3fd; }
        .foto img { width: 19mm; height: 24mm; }
        .foto .inisial { padding-top: 8mm; text-align: center; font-size: 13pt; font-weight: bold; color: #0e61a2; }

        .data { position: absolute; left: 25.5mm; top: 16mm; width: 37mm; }
        .data .nama { font-size: 7.6pt; font-weight: bold; line-height: 1.2; color: #0a294b; }
        .data .nim { font-size: 7pt; font-weight: bold; color: #1079c7; margin-top: 0.6mm; letter-spacing: 0.3pt; }
        .data table { margin-top: 1.4mm; border-collapse: collapse; }
        .data td { font-size: 5.2pt; padding: 0.35mm 0; vertical-align: top; color: #334155; }
        .data td.label { width: 13mm; color: #64748b; }

        .qr { position: absolute; right: 3.5mm; top: 16mm; width: 17mm; text-align: center; }
        .qr img { width: 17mm; height: 17mm; }
        .qr .ket { font-size: 4.2pt; color: #64748b; margin-top: 0.5mm; line-height: 1.2; }

        .kaki { position: absolute; left: 0; top: 47.3mm; width: 85.6mm; height: 6.5mm; background: #e7f3fd; }
        .kaki .kiri { position: absolute; left: 3.5mm; top: 1.9mm; font-size: 4.6pt; color: #0e61a2; }
        .kaki .kanan { position: absolute; right: 3.5mm; top: 1.9mm; font-size: 4.6pt; color: #0e61a2; font-weight: bold; }
        .status { display: inline; padding: 0.2mm 1.2mm; border-radius: 1mm; font-size: 4.8pt; font-weight: bold; color: #ffffff; background: #059669; }
        .status-Cuti { background: #d97706; }
        .status-Lulus { background: #1079c7; }
        .status-Keluar { background: #64748b; }

        .halaman-baru { page-break-after: always; }
    </style>
</head>
<body>
@php
    $render = function (array $item) use ($logo, $berlaku) {
        return view('mahasiswa._kartu', $item + ['logo' => $logo, 'berlaku' => $berlaku])->render();
    };
@endphp

@if ($tunggal)
    {!! $render($kartu->first()) !!}
@else
    @foreach ($kartu->chunk(10) as $halaman)
        <table class="lembar @if (! $loop->last) halaman-baru @endif">
            @foreach ($halaman->chunk(2) as $baris)
                <tr>
                    @foreach ($baris as $item)
                        <td class="sel">{!! $render($item) !!}</td>
                    @endforeach
                    @if ($baris->count() === 1)
                        <td class="sel"></td>
                    @endif
                </tr>
            @endforeach
        </table>
    @endforeach
@endif
</body>
</html>
