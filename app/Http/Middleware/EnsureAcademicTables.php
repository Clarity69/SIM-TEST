<?php

namespace App\Http\Middleware;

use App\Services\AcademicStats;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAcademicTables
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(AcademicStats::class)->get()['ready']) {
            return redirect()->route($request->is('dosen*') ? 'dosen.index' : 'mata-kuliah.index')
                ->with('error', 'Modul belum diaktifkan. Hubungi administrator kampus.');
        }

        return $next($request);
    }
}
