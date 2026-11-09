<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    @include('layouts.partials.head')
    <title>{{ $title }} · SIM Mahasiswa {{ config('stitek.singkatan') }}</title>
    {{-- Prerender halaman saat kursor berada di link (Chromium). PDF/ekspor dikecualikan. --}}
    <script type="speculationrules">
        {"prerender": [{"source": "document", "where": {"and": [
            {"href_matches": "/*"},
            {"not": {"href_matches": "/*pdf*"}},
            {"not": {"href_matches": "/*export*"}},
            {"not": {"selector_matches": "[target=_blank], [data-no-prerender]"}}
        ]}, "eagerness": "moderate"}]}
    </script>
</head>
<body class="h-full" x-data>
    <a href="#konten" class="sr-only z-[70] rounded-lg bg-white px-4 py-2 text-sm font-semibold text-primary-700 focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Lewati ke konten</a>

    {{-- Overlay drawer mobile --}}
    <div x-show="$store.sidebar.mobileOpen" x-cloak x-transition.opacity
        class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" x-on:click="$store.sidebar.mobileOpen = false" aria-hidden="true"></div>

    {{-- Sidebar: drawer di mobile (dialog), kolom tetap di desktop --}}
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-200/70 bg-white px-4 py-7 dark:border-slate-800 dark:bg-slate-900 transition-[transform,width] duration-200 lg:translate-x-0"
        x-bind:class="{ 'translate-x-0': $store.sidebar.mobileOpen, 'lg:w-20': $store.sidebar.collapsed, 'lg:w-[232px]': ! $store.sidebar.collapsed }"
        x-bind:role="$store.sidebar.mobileOpen ? 'dialog' : null"
        x-bind:aria-modal="$store.sidebar.mobileOpen ? 'true' : null"
        aria-label="Navigasi utama"
        x-trap.noscroll.inert="$store.sidebar.mobileOpen"
        x-bind:inert="$store.sidebar.isMobile && ! $store.sidebar.mobileOpen"
        x-on:keydown.escape.window="$store.sidebar.mobileOpen = false">
        <div class="flex items-center gap-3 px-2" x-bind:class="$store.sidebar.collapsed ? 'lg:justify-center lg:px-0' : ''">
            <img src="{{ asset('images/logo-stitek.webp') }}" alt="" width="40" height="40" class="size-10 rounded-full bg-white/10">
            <div class="min-w-0" x-bind:class="$store.sidebar.collapsed ? 'lg:hidden' : ''">
                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">SIM Mahasiswa</p>
                <p class="truncate text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ config('stitek.singkatan') }}</p>
            </div>
            <button type="button" class="ml-auto rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 lg:hidden"
                x-on:click="$store.sidebar.mobileOpen = false">
                <span class="sr-only">Tutup menu</span>
                <x-icon name="x" />
            </button>
        </div>

        <nav class="mt-8 flex-1 space-y-7 overflow-y-auto pb-6" aria-label="Menu">
            <div class="space-y-1">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400" x-bind:class="$store.sidebar.collapsed ? 'lg:sr-only' : ''">Workspace</p>
                <x-sidebar-link :href="route('dashboard')" icon="layout-dashboard" :active="request()->routeIs('dashboard')">Dashboard</x-sidebar-link>
                <x-sidebar-link :href="route('mahasiswa.index')" icon="users" :active="request()->routeIs('mahasiswa.*')">Data Mahasiswa</x-sidebar-link>
                <x-sidebar-link :href="route('dosen.index')" icon="user" :active="request()->routeIs('dosen.*')">Dosen</x-sidebar-link>
                <x-sidebar-link :href="route('mata-kuliah.index')" icon="file-text" :active="request()->routeIs('mata-kuliah.*')">Mata Kuliah</x-sidebar-link>
                <x-sidebar-link :href="route('prodi.index')" icon="graduation-cap" :active="request()->routeIs('prodi.*')">Program Studi</x-sidebar-link>
            </div>
            @can('admin')
                <div class="space-y-1">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400" x-bind:class="$store.sidebar.collapsed ? 'lg:sr-only' : ''">Admin</p>
                    <x-sidebar-link :href="route('users.index')" icon="user-cog" :active="request()->routeIs('users.*')">Pengguna</x-sidebar-link>
                    <x-sidebar-link :href="route('activity.index')" icon="history" :active="request()->routeIs('activity.*')">Log Aktivitas</x-sidebar-link>
                    <x-sidebar-link :href="route('trash.index')" icon="trash-2" :active="request()->routeIs('trash.*')">Sampah</x-sidebar-link>
                </div>
            @endcan
            <div class="space-y-1">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400" x-bind:class="$store.sidebar.collapsed ? 'lg:sr-only' : ''">Preferensi</p>
                <x-sidebar-link :href="route('profile.edit')" icon="user-round" :active="request()->routeIs('profile.*')">Profil Saya</x-sidebar-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800"
                        x-bind:class="$store.sidebar.collapsed ? 'lg:justify-center lg:px-0' : ''" x-bind:title="$store.sidebar.collapsed ? 'Keluar' : null">
                        <x-icon name="log-out" />
                        <span x-bind:class="$store.sidebar.collapsed ? 'lg:sr-only' : ''">Keluar</span>
                    </button>
                </form>
            </div>
        </nav>

        <div class="mb-5 rounded-xl bg-indigo-50/60 p-4 dark:bg-indigo-500/10" x-bind:class="$store.sidebar.collapsed ? 'lg:hidden' : ''">
            <p class="text-xs font-semibold text-slate-900 dark:text-white">Butuh bantuan?</p>
            <p class="mt-2 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">Hubungi layanan kampus untuk bantuan administrasi akademik.</p>
            <a href="mailto:{{ config('stitek.kontak.email') }}" class="mt-3 inline-flex min-h-8 items-center gap-1 text-xs font-semibold text-indigo-700 dark:text-indigo-300">Hubungi kampus <x-icon name="arrow-up-right" class="size-3.5" /></a>
        </div>

        <button type="button" class="hidden items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-medium text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800 lg:flex"
            x-bind:class="$store.sidebar.collapsed ? 'justify-center px-0' : ''"
            x-on:click="$store.sidebar.toggleCollapse()"
            x-bind:aria-expanded="(! $store.sidebar.collapsed).toString()" aria-controls="sidebar"
            x-bind:title="$store.sidebar.collapsed ? 'Tampilkan menu lengkap' : null">
            <x-icon name="panel-left-close" x-show="! $store.sidebar.collapsed" />
            <x-icon name="panel-left-open" x-show="$store.sidebar.collapsed" x-cloak />
            <span x-show="! $store.sidebar.collapsed">Ciutkan sidebar</span>
            <span class="sr-only" x-show="$store.sidebar.collapsed">Tampilkan menu lengkap</span>
        </button>
    </aside>

    <div class="flex min-h-full min-w-0 flex-col transition-[padding] duration-200"
        x-bind:class="$store.sidebar.collapsed ? 'lg:pl-20' : 'lg:pl-[232px]'">
        <header class="sticky top-0 z-30 flex h-16 min-w-0 items-center gap-2 border-b border-slate-200/80 bg-white/95 px-4 backdrop-blur sm:gap-3 sm:px-6 lg:h-20 lg:px-8 dark:border-slate-800 dark:bg-slate-900/95">
            <button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden dark:text-slate-300 dark:hover:bg-slate-800"
                x-on:click="$store.sidebar.mobileOpen = true" aria-controls="sidebar" x-bind:aria-expanded="$store.sidebar.mobileOpen.toString()">
                <span class="sr-only">Buka menu</span>
                <x-icon name="menu" />
            </button>

            <x-breadcrumb :items="$breadcrumbs" class="flex-1" />

            <button type="button" x-on:click="$dispatch('open-command-palette')"
                class="flex h-10 shrink-0 items-center gap-2 rounded-xl border border-transparent bg-slate-50 px-2.5 text-sm text-slate-500 hover:bg-slate-100 hover:text-slate-700 sm:w-56 xl:w-[300px] dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                aria-keyshortcuts="Control+K Meta+K /">
                <x-icon name="search" class="size-[18px]" />
                <span class="hidden flex-1 text-left sm:block">Cari mahasiswa, dosen…</span>
                <span class="sr-only sm:hidden">Cari atau jalankan perintah</span>
                <kbd class="kbd hidden sm:inline-flex">Ctrl K</kbd>
            </button>

            <button type="button" class="grid size-10 shrink-0 place-items-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                x-on:click="$store.theme.toggle()" x-bind:aria-pressed="$store.theme.dark.toString()">
                <span class="sr-only">Mode gelap</span>
                <x-icon name="moon" class="size-[18px]" x-show="! $store.theme.dark" />
                <x-icon name="sun" class="size-[18px]" x-show="$store.theme.dark" x-cloak />
            </button>

            <x-dropdown align="right" width="w-72" content-classes="bg-white p-5 dark:bg-slate-800">
                <x-slot:trigger>
                    <button type="button" class="grid size-10 shrink-0 place-items-center rounded-xl text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800" aria-label="Notifikasi">
                        <x-icon name="bell" class="size-5" />
                    </button>
                </x-slot:trigger>
                <x-slot:content>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Notifikasi</p>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">Notifikasi akademik belum tersedia.</p>
                </x-slot:content>
            </x-dropdown>

            <x-dropdown align="right" width="w-56" content-classes="py-1 bg-white dark:bg-slate-800">
                <x-slot name="trigger">
                    <button type="button" class="flex items-center gap-2.5 rounded-xl py-1 pl-1 pr-2.5 hover:bg-slate-50 dark:hover:bg-slate-800">
                        <x-avatar :name="Auth::user()->name" size="sm" />
                        <span class="hidden text-left sm:block">
                            <span class="block text-sm font-semibold leading-tight text-slate-900 dark:text-white">{{ Auth::user()->name }}</span>
                            <span class="block text-xs leading-tight text-slate-500 dark:text-slate-400">{{ \App\Models\User::ROLES[Auth::user()->role] ?? Auth::user()->role }}</span>
                        </span>
                        <x-icon name="chevron-down" class="size-4 text-slate-400" />
                        <span class="sr-only">Menu profil</span>
                    </button>
                </x-slot>
                <x-slot name="content">
                    <div class="border-b border-slate-100 px-4 py-2.5 dark:border-slate-700 sm:hidden">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ Auth::user()->name }}</p>
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ Auth::user()->email }}</p>
                    </div>
                    <button type="button" class="flex w-full items-center gap-2.5 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 focus:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700 dark:focus:bg-slate-700"
                        x-on:click="$store.account.open('profile')">
                        <x-icon name="user-round" class="size-4 text-slate-400" /> Profil Saya
                    </button>
                    <button type="button" class="flex w-full items-center gap-2.5 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 focus:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-700 dark:focus:bg-slate-700"
                        x-on:click="$store.account.open('password')">
                        <x-icon name="key-round" class="size-4 text-slate-400" /> Ganti Password
                    </button>
                    <div class="my-1 border-t border-slate-100 dark:border-slate-700"></div>
                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2.5 px-4 py-2 text-left text-sm text-red-700 hover:bg-red-50 focus:bg-red-50 dark:text-red-300 dark:hover:bg-red-500/10 dark:focus:bg-red-500/10">
                            <x-icon name="log-out" class="size-4" /> Keluar
                        </button>
                    </form>
                </x-slot>
            </x-dropdown>
        </header>

        <main id="konten" class="min-w-0 flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto min-w-0 max-w-7xl space-y-6">
                {{ $slot }}
            </div>
        </main>

        <footer class="mb-20 border-t border-slate-200 px-4 py-5 text-xs text-slate-500 sm:px-6 lg:mb-0 lg:px-8 dark:border-slate-800 dark:text-slate-400">
            <div class="mx-auto flex max-w-7xl flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ config('stitek.nama') }}</p>
                <p>Sistem Informasi Mahasiswa <span class="mx-1.5 text-slate-300" aria-hidden="true">/</span> {{ config('stitek.kontak.email') }}</p>
            </div>
        </footer>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-3 border-t border-slate-200 bg-white/95 px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 backdrop-blur lg:hidden dark:border-slate-800 dark:bg-slate-900/95" aria-label="Navigasi cepat">
        @foreach (['dashboard' => ['layout-dashboard', 'Dashboard', 'dashboard'], 'mahasiswa.index' => ['users', 'Mahasiswa', 'mahasiswa.*'], 'prodi.index' => ['graduation-cap', 'Program Studi', 'prodi.*']] as $destination => [$icon, $label, $pattern])
            <a href="{{ route($destination) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif
                @class(['flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl text-[11px] font-semibold', 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-indigo-300' => request()->routeIs($pattern), 'text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800' => ! request()->routeIs($pattern)])>
                <x-icon :name="$icon" class="size-5" />{{ $label }}
            </a>
        @endforeach
    </nav>

    <x-flash />
    <x-modal-confirm />
    <x-account-modals />
    <x-command-palette />
    @stack('scripts')
</body>
</html>
