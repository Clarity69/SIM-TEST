{{-- $penandatangan: ['nama' => ?string, 'jabatan' => string, 'nidn' => ?string, 'label_nomor' => string]. Nama kosong = ditulis tangan. --}}
<table class="ttd">
    <tr>
        <td></td>
        <td class="kanan">
            {{ config('stitek.kop.kota') }}, {{ now()->locale('id')->translatedFormat('d F Y') }}<br>
            {{ $penandatangan['jabatan'] }},
            <div class="ruang"></div>
            @if (filled($penandatangan['nama']))
                <span class="nama">{{ $penandatangan['nama'] }}</span>
            @else
                ( ........................................ )
            @endif
            @if (filled($penandatangan['nidn']))
                <br>{{ $penandatangan['label_nomor'] ?? 'NIDN' }}. {{ $penandatangan['nidn'] }}
            @endif
        </td>
    </tr>
</table>
