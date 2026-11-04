<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chrome mengirim "Sec-Purpose: prefetch;prerender" saat prerender (Speculation Rules),
 * sedangkan Request::prefetch() Laravel hanya mengenali nilai persis "prefetch".
 * Tanpa normalisasi, halaman yang di-prerender menimpa URL sebelumnya di session
 * sehingga redirect back() setelah error validasi bisa mengarah ke halaman yang salah.
 */
class NormalizePrefetchHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        if (str_starts_with(strtolower((string) $request->headers->get('Sec-Purpose')), 'prefetch')) {
            $request->headers->set('Purpose', 'prefetch');
        }

        return $next($request);
    }
}
