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
        class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-primary-950 px-4 py-5 transition-[transform,width] duration-200 lg:translate-x-0"
        x-bind:class="{ 'translate-x-0': $store.sidebar.mobileOpen, 'lg:w-20': $store.sidebar.collapsed, 'lg:w-64': ! $store.sidebar.collapsed }"
        x-bind:role="$store.sidebar.mobileOpen ? 'dialog' : null"
        x-bind:aria-modal="$store.sidebar.mobileOpen ? 'true' : null"
        aria-label="Navigasi utama"
        x-trap.inert="$store.sidebar.mobileOpen"
        x-on:keydown.escape.window="$store.sidebar.mobileOpen = false">
        <div class="flex items-center gap-3 px-2" x-bind:class="$store.sidebar.collapsed ? 'lg:justify-center lg:px-0' : ''">
            <img src="{{ asset('images/logo-stitek.webp') }}" alt="" width="40" height="40" class="size-10 rounded-full bg-white/10">
            <div class="min-w-0" x-bind:class="$store.sidebar.collapsed ? 'lg:hidden' : ''">
                <p class="truncate text-base font-bold text-white">SIM Mahasiswa</p>
                <p class="truncate text-xs text-primary-200">{{ config('stitek.singkatan') }}</p>
            </div>
            <button type="button" class="ml-auto rounded-lg p-1.5 text-primary-200 hover:bg-white/10 hover:text-white lg:hidden"
                x-on:click="$store.sidebar.mobileOpen = false">
                <span class="sr-only">Tutup menu</span>
                <x-icon name="x" />
            </button>
        </div>

        <nav class="mt-8 flex-1 space-y-6 overflow-y-auto" aria-label="Menu">
            <div class="space-y-1">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-primary-300" x-bind:class="$store.sidebar.collapsed ? 'lg:sr-only' : ''">Menu utama</p>
                <x-sidebar-link :href="route('dashboard')" icon="layout-dashboard" :active="request()->routeIs('dashboard')">Dashboard</x-sidebar-link>
                <x-sidebar-link :href="route('mahasiswa.index')" icon="users" :active="request()->routeIs('mahasiswa.*')">Data Mahasiswa</x-sidebar-link>
                <x-sidebar-link :href="route('prodi.index')" icon="graduation-cap" :active="request()->routeIs('prodi.*')">Program Studi</x-sidebar-link>
            </div>
            <div class="space-y-1">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-primary-300" x-bind:class="$store.sidebar.collapsed ? 'lg:sr-only' : ''">Akun</p>
                <x-sidebar-link :href="route('profile.edit')" icon="user-round" :active="request()->routeIs('profile.*')">Profil Saya</x-sidebar-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-primary-100 transition hover:bg-white/5 hover:text-white"
                        x-bind:class="$store.sidebar.collapsed ? 'lg:justify-center lg:px-0' : ''" x-bind:title="$store.sidebar.collapsed ? 'Keluar' : null">
                        <x-icon name="log-out" />
                        <span x-bind:class="$store.sidebar.collapsed ? 'lg:sr-only' : ''">Keluar</span>
                    </button>
                </form>
            </div>
        </nav>

        <button type="button" class="hidden items-center gap-3 rounded-xl border border-white/10 px-3 py-2.5 text-sm font-medium text-primary-100 hover:bg-white/5 hover:text-white lg:flex"
            x-bind:class="$store.sidebar.collapsed ? 'justify-center px-0' : ''"
            x-on:click="$store.sidebar.toggleCollapse()"
            x-bind:aria-expanded="(! $store.sidebar.collapsed).toString()" aria-controls="sidebar">
            <x-icon name="panel-left-close" x-show="! $store.sidebar.collapsed" />
            <x-icon name="panel-left-open" x-show="$store.sidebar.collapsed" x-cloak />
            <span x-show="! $store.sidebar.collapsed">Ciutkan sidebar</span>
            <span class="sr-only" x-show="$store.sidebar.collapsed">Lebarkan sidebar</span>
        </button>
    </aside>

    <div class="flex min-h-full flex-col transition-[padding] duration-200"
        x-bind:class="$store.sidebar.collapsed ? 'lg:pl-20' : 'lg:pl-64'">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8 dark:border-slate-800 dark:bg-slate-900/90">
            <button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden dark:text-slate-300 dark:hover:bg-slate-800"
                x-on:click="$store.sidebar.mobileOpen = true" aria-controls="sidebar" x-bind:aria-expanded="$store.sidebar.mobileOpen.toString()">
                <span class="sr-only">Buka menu</span>
                <x-icon name="menu" />
            </button>

            <x-breadcrumb :items="$breadcrumbs" class="flex-1" />

            <button type="button" class="grid size-9 place-items-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                x-on:click="$store.theme.toggle()" x-bind:aria-pressed="$store.theme.dark.toString()">
                <span class="sr-only">Mode gelap</span>
                <x-icon name="moon" class="size-[18px]" x-show="! $store.theme.dark" />
                <x-icon name="sun" class="size-[18px]" x-show="$store.theme.dark" x-cloak />
            </button>

            <x-dropdown align="right" width="w-56" content-classes="py-1 bg-white dark:bg-slate-800">
                <x-slot name="trigger">
                    <button type="button" class="flex items-center gap-2.5 rounded-full border border-slate-200 py-1 pl-1 pr-2.5 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                        <x-avatar :name="Auth::user()->name" size="sm" />
                        <span class="hidden text-left sm:block">
                            <span class="block text-sm font-semibold leading-tight text-slate-900 dark:text-white">{{ Auth::user()->name }}</span>
                            <span class="block text-xs leading-tight text-slate-500 dark:text-slate-400">{{ Auth::user()->email }}</span>
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
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2.5 px-4 py-2 text-left text-sm text-red-700 hover:bg-red-50 focus:bg-red-50 dark:text-red-300 dark:hover:bg-red-500/10 dark:focus:bg-red-500/10">
                            <x-icon name="log-out" class="size-4" /> Keluar
                        </button>
                    </form>
                </x-slot>
            </x-dropdown>
        </header>

        <main id="konten" class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto max-w-7xl space-y-6">
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-slate-200 px-4 py-6 text-xs text-slate-500 sm:px-6 lg:px-8 dark:border-slate-800 dark:text-slate-400">
            <div class="mx-auto flex max-w-7xl flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ config('stitek.nama') }} · {{ config('stitek.kontak.alamat') }}</p>
                <p>{{ config('stitek.kontak.telepon') }} · {{ config('stitek.kontak.email') }} · IG {{ config('stitek.kontak.instagram') }} · FB {{ config('stitek.kontak.facebook') }}</p>
            </div>
        </footer>
    </div>

    <x-flash />
    <x-modal-confirm />
    <x-account-modals />
    @stack('scripts')
</body>
</html>
