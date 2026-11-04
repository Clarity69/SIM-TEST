@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
<img src="{{ config('stitek.logo_url') }}" class="logo" width="56" height="56" alt="Logo STITEK Bontang">
<span class="brand-name">{{ $slot }}</span>
</a>
</td>
</tr>
