<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
SIM Mahasiswa · {{ config('stitek.singkatan') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
{{ config('stitek.nama') }} — {{ config('stitek.kontak.alamat') }} — {{ config('stitek.kontak.telepon') }} · {{ config('stitek.kontak.email') }}
© {{ date('Y') }} SIM Mahasiswa {{ config('stitek.singkatan') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
