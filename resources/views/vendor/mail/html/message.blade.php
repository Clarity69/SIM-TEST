<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
SIM Mahasiswa · {{ config('stitek.singkatan') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
**{{ config('stitek.nama') }}**<br>
{{ config('stitek.kontak.alamat') }}<br>
{{ config('stitek.kontak.telepon') }} · {{ config('stitek.kontak.email') }}<br>
IG {{ config('stitek.kontak.instagram') }} · FB {{ config('stitek.kontak.facebook') }}

© {{ date('Y') }} SIM Mahasiswa {{ config('stitek.singkatan') }}. Email ini dikirim otomatis, mohon tidak membalas.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
