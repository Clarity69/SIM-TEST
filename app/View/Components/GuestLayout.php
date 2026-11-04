<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Layout split untuk halaman autentikasi: panel kiri branding STITEK, panel kanan form.
 */
class GuestLayout extends Component
{
    public function __construct(
        public string $title = 'Masuk',
        public string $tagline = 'Kelola data mahasiswa STITEK dalam satu tempat.',
    ) {}

    public function render(): View
    {
        return view('layouts.guest');
    }
}
