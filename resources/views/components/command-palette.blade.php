@php
    use App\View\Components\Icon;

    $perintah = [
        ['group' => 'Navigasi', 'title' => 'Dashboard', 'icon' => 'layout-dashboard', 'url' => route('dashboard'), 'keywords' => 'beranda statistik'],
        ['group' => 'Navigasi', 'title' => 'Data Mahasiswa', 'icon' => 'users', 'url' => route('mahasiswa.index'), 'keywords' => 'daftar mahasiswa'],
        ['group' => 'Navigasi', 'title' => 'Dosen', 'icon' => 'user', 'url' => route('dosen.index'), 'keywords' => 'dosen pengajar aktif nidn'],
        ['group' => 'Navigasi', 'title' => 'Mata Kuliah', 'icon' => 'file-text', 'url' => route('mata-kuliah.index'), 'keywords' => 'matkul kurikulum sks semester'],
        ['group' => 'Navigasi', 'title' => 'Program Studi', 'icon' => 'graduation-cap', 'url' => route('prodi.index'), 'keywords' => 'prodi jurusan kaprodi'],
        ['group' => 'Navigasi', 'title' => 'Profil Saya', 'icon' => 'user-round', 'url' => route('profile.edit'), 'keywords' => 'akun notifikasi'],
    ];

    if (auth()->user()?->isAdmin()) {
        array_push($perintah,
            ['group' => 'Admin', 'title' => 'Pengguna', 'icon' => 'user-cog', 'url' => route('users.index'), 'keywords' => 'role admin operator akun'],
            ['group' => 'Admin', 'title' => 'Log Aktivitas', 'icon' => 'history', 'url' => route('activity.index'), 'keywords' => 'riwayat audit'],
            ['group' => 'Admin', 'title' => 'Sampah', 'icon' => 'trash-2', 'url' => route('trash.index'), 'keywords' => 'pulihkan terhapus'],
        );
    }

    array_push($perintah,
        ['group' => 'Aksi cepat', 'title' => 'Tambah mahasiswa', 'icon' => 'user-plus', 'url' => route('mahasiswa.create'), 'keywords' => 'baru input'],
        ['group' => 'Aksi cepat', 'title' => 'Tambah program studi', 'icon' => 'plus', 'url' => route('prodi.create'), 'keywords' => 'prodi baru'],
        ['group' => 'Aksi cepat', 'title' => 'Impor mahasiswa dari Excel/CSV', 'icon' => 'upload', 'url' => route('mahasiswa.import'), 'keywords' => 'import unggah'],
        ['group' => 'Aksi cepat', 'title' => 'Ekspor semua mahasiswa ke Excel', 'icon' => 'download', 'url' => route('mahasiswa.export'), 'keywords' => 'export xlsx unduh'],
        ['group' => 'Aksi cepat', 'title' => 'Cetak PDF data mahasiswa', 'icon' => 'printer', 'url' => route('mahasiswa.pdf'), 'keywords' => 'laporan print'],
        ['group' => 'Akun & tampilan', 'title' => 'Ganti password', 'icon' => 'key-round', 'action' => 'password', 'keywords' => 'kata sandi keamanan'],
        ['group' => 'Akun & tampilan', 'title' => 'Ganti mode gelap / terang', 'icon' => 'moon', 'action' => 'theme', 'keywords' => 'dark light tema'],
        ['group' => 'Akun & tampilan', 'title' => 'Keluar', 'icon' => 'log-out', 'action' => 'logout', 'keywords' => 'logout sign out'],
    );

    $ikon = collect($perintah)->pluck('icon')->push('user-round', 'graduation-cap')->unique()
        ->mapWithKeys(fn ($nama) => [$nama => Icon::svg($nama, 'size-[18px] shrink-0')])
        ->put('mahasiswa', Icon::svg('user-round', 'size-[18px] shrink-0'))
        ->put('prodi', Icon::svg('graduation-cap', 'size-[18px] shrink-0'));
@endphp

<div x-data="commandPalette({{ \Illuminate\Support\Js::from(['commands' => $perintah, 'searchUrl' => route('search'), 'icons' => $ikon]) }})"
    x-on:keydown.escape.window="open && close()">
    <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-start justify-center px-4 pt-[12vh]" role="dialog" aria-modal="true" aria-label="Palet perintah">
        <div class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm" x-show="open" x-transition.opacity x-on:click="close()" aria-hidden="true"></div>

        <div class="relative w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800"
            x-show="open" x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100"
            x-trap.noscroll.inert="open">
            <div class="relative flex items-center border-b border-slate-200 dark:border-slate-800">
                <span class="pointer-events-none absolute left-4 text-slate-400">
                    <span x-show="! loading"><x-icon name="search" class="size-5" /></span>
                    <span x-show="loading" x-cloak class="block animate-spin"><x-icon name="loader-circle" class="size-5" /></span>
                </span>
                <input x-ref="input" type="text" x-model="query" x-on:input="search()"
                    x-on:keydown.arrow-down.prevent="move(1)" x-on:keydown.arrow-up.prevent="move(-1)" x-on:keydown.enter.prevent="choose()"
                    role="combobox" aria-expanded="true" aria-controls="cmd-list" aria-autocomplete="list"
                    x-bind:aria-activedescendant="items.length ? 'cmd-item-' + active : null"
                    placeholder="Cari mahasiswa (NIM/nama), prodi, atau perintah…" autocomplete="off" spellcheck="false"
                    class="h-14 w-full border-0 bg-transparent pl-12 pr-16 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-0 dark:text-white">
                <kbd class="absolute right-4 rounded-md border border-slate-200 px-1.5 py-0.5 text-[11px] font-medium text-slate-500 dark:border-slate-700 dark:text-slate-400">Esc</kbd>
            </div>

            <ul id="cmd-list" role="listbox" aria-label="Hasil" class="max-h-[min(60vh,26rem)] overflow-y-auto p-2">
                <template x-for="(item, index) in items" :key="item.group + item.title + (item.url ?? item.action)">
                    <li role="presentation">
                        <p x-show="startsGroup(index)" class="px-3 pb-1.5 pt-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 first:pt-1 dark:text-slate-400" x-text="item.group" aria-hidden="true"></p>
                        <div x-bind:id="'cmd-item-' + index" role="option" x-bind:aria-selected="(active === index).toString()"
                            x-on:click="choose(item)" x-on:mousemove="active = index"
                            class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2.5 text-sm"
                            x-bind:class="active === index ? 'bg-primary-50 text-primary-900 dark:bg-primary-500/15 dark:text-white' : 'text-slate-700 dark:text-slate-200'">
                            <span class="grid size-8 shrink-0 place-items-center rounded-lg"
                                x-bind:class="active === index ? 'bg-white text-primary-700 shadow-sm dark:bg-slate-900 dark:text-primary-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'"
                                x-html="icons[item.icon]"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-medium" x-text="item.title"></span>
                                <span x-show="item.subtitle" class="block truncate text-xs text-slate-500 dark:text-slate-400" x-text="item.subtitle"></span>
                            </span>
                            <span x-show="active === index" class="text-slate-400" aria-hidden="true"><x-icon name="corner-down-left" class="size-4" /></span>
                        </div>
                    </li>
                </template>

                <li x-show="items.length === 0" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400" role="presentation">
                    <span x-show="loading">Mencari…</span>
                    <span x-show="! loading">Tidak ada hasil untuk “<span x-text="query"></span>”.</span>
                </li>
            </ul>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 border-t border-slate-200 px-4 py-2.5 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400" aria-hidden="true">
                <span class="flex items-center gap-1.5"><kbd class="kbd">↑</kbd><kbd class="kbd">↓</kbd> pilih</span>
                <span class="flex items-center gap-1.5"><kbd class="kbd">Enter</kbd> buka</span>
                <span class="flex items-center gap-1.5"><kbd class="kbd">/</kbd> atau <kbd class="kbd" x-text="isMac ? '⌘ K' : 'Ctrl K'">Ctrl K</kbd> kapan saja</span>
            </div>
        </div>
    </div>
</div>
