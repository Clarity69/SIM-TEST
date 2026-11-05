<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Log aktivitas (khusus admin): siapa melakukan apa dan kapan.
 */
class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->trim()->toString() ?: null,
            'event' => $request->input('event'),
            'user_id' => $request->input('user_id'),
            'subject_type' => $request->input('subject_type'),
        ];

        // simplePaginate: tanpa COUNT(*) sehingga hanya satu query ke database per halaman.
        $logs = ActivityLog::query()
            ->select('id', 'user_id', 'user_name', 'event', 'subject_type', 'subject_id', 'subject_label', 'description', 'properties', 'ip_address', 'created_at')
            ->filter($filters)
            ->latest('created_at')
            ->latest('id')
            ->simplePaginate(25)
            ->withQueryString();

        $users = User::query()->orderBy('name')->pluck('name', 'id');
        $namaProdi = Prodi::opsi()->pluck('nama_prodi', 'id')->all();

        return view('activity.index', compact('logs', 'users', 'filters', 'namaProdi'));
    }
}
