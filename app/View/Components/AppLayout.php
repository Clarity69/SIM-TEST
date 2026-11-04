<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Layout aplikasi (sidebar + topbar): <x-app-layout title="..." :breadcrumbs="['Data Mahasiswa' => null]">.
 */
class AppLayout extends Component
{
    /**
     * @param  array<string, string|null>  $breadcrumbs  label => url (null untuk halaman aktif)
     */
    public function __construct(
        public string $title = 'Dashboard',
        public array $breadcrumbs = [],
    ) {}

    public function render(): View
    {
        return view('layouts.app');
    }
}
