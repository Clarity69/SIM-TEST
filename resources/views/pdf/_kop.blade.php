@php
    $logoPath = public_path(config('stitek.logo'));
    $logo = is_file($logoPath)
        ? 'data:'.mime_content_type($logoPath).';base64,'.base64_encode(file_get_contents($logoPath))
        : null;
@endphp
<table class="kop">
    <tr>
        <td class="logo">
            @if ($logo)
                <img src="{{ $logo }}" alt="Logo STITEK">
            @endif
        </td>
        <td class="teks">
            <div class="yayasan">{{ config('stitek.kop.yayasan') }}</div>
            <div class="instansi">{{ config('stitek.kop.instansi') }}</div>
            <div class="fakultas">{{ config('stitek.kop.fakultas') }}</div>
            <div class="alamat">{{ config('stitek.kop.alamat') }}<br>{{ config('stitek.kop.kontak') }}</div>
        </td>
        <td class="penyeimbang"></td>
    </tr>
</table>
<hr class="garis-kop">
