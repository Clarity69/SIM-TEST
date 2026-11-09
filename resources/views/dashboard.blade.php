<x-app-layout title="Dashboard" :breadcrumbs="['Dashboard' => null]">
    @php
        $grafikProdi = [
            'type' => 'bar',
            'labels' => $mahasiswaPerProdi->pluck('label'),
            'names' => $mahasiswaPerProdi->pluck('nama_prodi'),
            'values' => $mahasiswaPerProdi->pluck('mahasiswas_count'),
        ];
        $grafikGender = ['type' => 'doughnut', 'labels' => ['Laki-laki', 'Perempuan'], 'values' => [$totalLakiLaki, $totalPerempuan]];

        $daftarStatus = array_keys(\App\Models\Mahasiswa::STATUS);
        $tahunAngkatan = collect($perAngkatan)->pluck('angkatan')->unique()->sort()->values();
        $grafikAngkatan = [
            'type' => 'stacked',
            'labels' => $tahunAngkatan,
            'datasets' => collect($daftarStatus)->map(fn ($status) => [
                'status' => $status,
                'values' => $tahunAngkatan->map(fn ($tahun) => (int) collect($perAngkatan)->where('angkatan', $tahun)->where('status', $status)->sum('jumlah')),
            ]),
        ];
        $warnaStatus = ['Aktif' => 'bg-emerald-500 dark:bg-emerald-400', 'Cuti' => 'bg-amber-500 dark:bg-amber-400', 'Lulus' => 'bg-primary-600 dark:bg-primary-400', 'Keluar' => 'bg-slate-400 dark:bg-slate-500'];
        $penerimaan = collect($perAngkatan)->groupBy('angkatan')->sortKeys()->map(fn ($rows) => $rows->sum('jumlah'));
        $grafikPenerimaan = ['type' => 'bar', 'tone' => 'campus', 'labels' => $penerimaan->keys()->values(), 'values' => $penerimaan->values()];
    @endphp
    <x-page-header title="Dashboard" :eyebrow="'Tahun akademik '.(now()->month >= 7 ? now()->year : now()->year - 1).' / '.(now()->month >= 7 ? now()->year + 1 : now()->year)" :description="'Selamat datang, '.\Illuminate\Support\Str::before(auth()->user()->name, ' ').'. Berikut ringkasan aktivitas akademik hari ini.'">
        <span class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-white px-4 text-xs font-medium text-slate-600 ring-1 ring-slate-200/70 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-800">
            <x-icon name="calendar-range" class="size-4 text-indigo-600 dark:text-indigo-400" />
            {{ now()->locale('id')->translatedFormat('d F Y') }}
        </span>
    </x-page-header>

    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4" aria-label="Ringkasan kampus">
        <x-stat-card label="Total Mahasiswa" :value="$totalMahasiswa" icon="users" :trend="$tren['total']" :delta="$selisih['total']" :since="$terakhir['total']" :href="route('mahasiswa.index')" />
        <x-stat-card label="Dosen Aktif" :value="$totalDosenAktif" icon="user" tone="success" :href="route('dosen.index', ['aktif' => 1])" />
        <x-stat-card label="Mata Kuliah" :value="$totalMataKuliah" icon="file-text" :href="route('mata-kuliah.index')" />
        <x-stat-card label="Program Studi" :value="$totalProdi" icon="building-2" tone="success" :trend="$tren['prodi']" :delta="$selisih['prodi']" :since="$terakhir['prodi']" :href="route('prodi.index')" />
    </div>

    <div class="grid min-w-0 grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(19rem,1fr)]">
        <x-card title="Tren Mahasiswa per Angkatan" description="Jumlah mahasiswa terdaftar berdasarkan tahun angkatan">
            <x-slot:actions>
                <span class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500 dark:bg-slate-800 dark:text-slate-400">Seluruh angkatan</span>
            </x-slot:actions>
            @if ($penerimaan->isNotEmpty())
                <div class="flex justify-end gap-2 pb-4 text-xs text-slate-500 dark:text-slate-400">
                    <span class="size-2 self-center rounded-full bg-indigo-600 dark:bg-indigo-400" aria-hidden="true"></span> Mahasiswa terdaftar
                </div>
                <div class="h-48 min-w-0">
                    <canvas role="img" aria-label="Grafik jumlah mahasiswa terdaftar per angkatan" data-chart="{{ json_encode($grafikPenerimaan) }}"></canvas>
                </div>
                <ul class="sr-only">
                    @foreach ($penerimaan as $tahun => $jumlah)
                        <li>Angkatan {{ $tahun }}: {{ $jumlah }} mahasiswa</li>
                    @endforeach
                </ul>
            @else
                <div class="flex min-h-64 items-center justify-center">
                    <x-empty-state icon="chart-column" title="Belum ada data angkatan" description="Grafik akan tampil setelah tahun angkatan mahasiswa diisi." />
                </div>
            @endif
        </x-card>
        <x-academic-calendar />

        <x-card title="Aktivitas Mahasiswa Terbaru" description="5 mahasiswa terakhir yang ditambahkan" :padding="false">
            <x-slot:actions>
                <a href="{{ route('mahasiswa.index') }}" class="inline-flex min-h-8 items-center gap-1 text-xs font-semibold text-indigo-700 hover:text-indigo-800 dark:text-indigo-300">
                    Lihat semua <x-icon name="arrow-right" class="size-4" />
                </a>
            </x-slot:actions>
            @if ($mahasiswaTerbaru->isEmpty())
                <x-empty-state title="Belum ada mahasiswa" description="Tambahkan mahasiswa pertama untuk mulai mengisi dashboard.">
                    <x-button icon="plus" :href="route('mahasiswa.create')">Tambah Mahasiswa</x-button>
                </x-empty-state>
            @else
                <div class="mt-5 overflow-x-auto rounded-b-2xl">
                    <table class="w-full text-left">
                        <caption class="sr-only">Mahasiswa terbaru, program studi, aktivitas, dan waktu penambahan</caption>
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                @foreach (['Mahasiswa', 'Program Studi', 'Aktivitas', 'Waktu'] as $kolom)
                                    <th scope="col" class="table-th text-[10px] uppercase tracking-wider">{{ $kolom }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($mahasiswaTerbaru as $mahasiswa)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="table-td">
                                        <a href="{{ route('mahasiswa.show', $mahasiswa['id']) }}" class="flex items-center gap-3 font-medium text-slate-900 hover:text-indigo-700 dark:text-white dark:hover:text-indigo-300">
                                            <x-avatar :name="$mahasiswa['nama']" :jenis-kelamin="$mahasiswa['jenis_kelamin']" size="sm" />
                                            <span class="flex flex-col gap-1 whitespace-nowrap text-xs">
                                                {{ $mahasiswa['nama'] }}
                                                <span class="text-[11px] font-normal tabular-nums text-slate-500 dark:text-slate-400">{{ $mahasiswa['nim'] }}</span>
                                            </span>
                                        </a>
                                    </td>
                                    <td class="table-td text-xs text-slate-600 dark:text-slate-300">{{ $mahasiswa['prodi'] }}</td>
                                    <td class="table-td"><x-badge tone="success">Data ditambahkan</x-badge></td>
                                    <td class="table-td whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Carbon::parse($mahasiswa['created_at'])->locale('id')->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
        <x-dashboard-announcements />
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/70 pt-6 dark:border-slate-800">
        <div>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Analisis Akademik</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Rincian komposisi dan sebaran mahasiswa.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-button variant="secondary" icon="printer" :href="route('mahasiswa.pdf')" target="_blank">Cetak PDF</x-button>
            <x-button icon="plus" :href="route('mahasiswa.create')">Tambah Mahasiswa</x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card title="Mahasiswa per Program Studi" description="Jumlah mahasiswa terdaftar di setiap prodi" class="lg:col-span-2">
            @if ($totalMahasiswa > 0)
                <div class="h-64 min-w-0">
                    <canvas role="img" aria-label="Grafik batang jumlah mahasiswa per program studi" data-chart="{{ json_encode($grafikProdi) }}"></canvas>
                </div>
            @else
                <x-empty-state icon="graduation-cap" title="Belum ada data" description="Grafik muncul setelah data mahasiswa ditambahkan." />
            @endif
        </x-card>
        <x-card title="Komposisi Gender" description="Perbandingan mahasiswa terdaftar">
            @if ($totalMahasiswa > 0)
                <div class="relative mx-auto h-44 w-44">
                    <canvas role="img" aria-label="Diagram komposisi gender" data-chart="{{ json_encode($grafikGender) }}"></canvas>
                </div>
                <dl class="mt-5 flex justify-center gap-6 text-xs text-slate-600 dark:text-slate-300">
                    <div class="flex flex-col gap-1"><dt>Laki-laki</dt><dd class="font-semibold">{{ $totalLakiLaki }}</dd></div>
                    <div class="flex flex-col gap-1"><dt>Perempuan</dt><dd class="font-semibold">{{ $totalPerempuan }}</dd></div>
                </dl>
            @else
                <x-empty-state icon="users" title="Belum ada data" />
            @endif
        </x-card>
    </div>

    <x-card title="Mahasiswa per Angkatan & Status" description="Sebaran status akademik setiap angkatan; pilih rentang angkatan untuk mempersempit"
        x-data="angkatanChart({{ \Illuminate\Support\Js::from(['rows' => $perAngkatan, 'statuses' => $daftarStatus]) }})">
        @if ($tahunAngkatan->isEmpty())
            <x-empty-state icon="chart-column" title="Belum ada data angkatan" description="Isi kolom angkatan pada data mahasiswa untuk menampilkan grafik ini." />
        @else
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($tahunAngkatan->count() > 4)
                        <div class="flex rounded-xl border border-slate-200 p-0.5 text-xs font-medium dark:border-slate-700" role="group" aria-label="Rentang cepat">
                            @foreach ([4 => '4 terakhir', 0 => 'Semua'] as $jumlah => $label)
                                <button type="button" class="rounded-lg px-2.5 py-1"
                                    x-on:click="preset({{ $jumlah ?: 'years.length' }})"
                                    x-bind:class="isPreset({{ $jumlah ?: 'years.length' }}) ? 'bg-primary-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
                                    x-bind:aria-pressed="isPreset({{ $jumlah ?: 'years.length' }}).toString()">{{ $label }}</button>
                            @endforeach
                        </div>
                    @endif
                    <div class="flex items-center gap-1.5 text-sm">
                        <x-icon name="calendar-range" class="size-4 text-slate-400" />
                        <label for="angkatan-dari" class="sr-only">Dari angkatan</label>
                        <select id="angkatan-dari" x-model.number="dari" class="form-control w-auto py-1.5 pr-8 text-sm">
                            @foreach ($tahunAngkatan as $tahun)
                                <option value="{{ $tahun }}">{{ $tahun }}</option>
                            @endforeach
                        </select>
                        <span class="text-slate-400" aria-hidden="true">–</span>
                        <label for="angkatan-sampai" class="sr-only">Sampai angkatan</label>
                        <select id="angkatan-sampai" x-model.number="sampai" class="form-control w-auto py-1.5 pr-8 text-sm">
                            @foreach ($tahunAngkatan as $tahun)
                                <option value="{{ $tahun }}" @selected($loop->last)>{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-slot:actions>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_16rem]">
                <div class="h-72 min-w-0">
                    <canvas x-ref="canvas" role="img" aria-label="Grafik batang bertumpuk jumlah mahasiswa per angkatan menurut status"
                        data-chart="{{ json_encode($grafikAngkatan) }}"></canvas>
                </div>
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        <span class="text-2xl font-bold tabular-nums text-slate-900 dark:text-white" x-text="total">{{ collect($perAngkatan)->sum('jumlah') }}</span>
                        mahasiswa · angkatan <span x-text="dari === sampai ? dari : dari + '–' + sampai">{{ $tahunAngkatan->first() }}–{{ $tahunAngkatan->last() }}</span>
                    </p>
                    <ul class="mt-4 space-y-3.5" aria-live="polite">
                        @foreach ($daftarStatus as $status)
                            <li>
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="size-2.5 rounded-sm {{ $warnaStatus[$status] }}" aria-hidden="true"></span>
                                    <span class="flex-1 text-slate-600 dark:text-slate-300">{{ $status }}</span>
                                    <span class="font-semibold tabular-nums text-slate-900 dark:text-white" x-text="jumlah(@js($status))"></span>
                                    <span class="w-12 text-right text-xs tabular-nums text-slate-500 dark:text-slate-400" x-text="persen(@js($status)).toFixed(1).replace('.', ',') + '%'"></span>
                                </div>
                                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800" aria-hidden="true">
                                    <div class="h-full rounded-full transition-[width] duration-500 {{ $warnaStatus[$status] }}" x-bind:style="{ width: persen(@js($status)) + '%' }"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </x-card>

    <x-card title="Rekapitulasi Program Studi" description="Jumlah dan komposisi mahasiswa di setiap program studi">
        <x-slot:actions>
            <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400" aria-hidden="true">
                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-primary-600 dark:bg-primary-400"></span> Laki-laki</span>
                <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-secondary-500"></span> Perempuan</span>
            </div>
        </x-slot:actions>

        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($mahasiswaPerProdi as $prodi)
                @php
                    $persen = $totalMahasiswa > 0 ? $prodi['mahasiswas_count'] / $totalMahasiswa * 100 : 0;
                    $persenL = $prodi['mahasiswas_count'] > 0 ? $prodi['laki_count'] / $prodi['mahasiswas_count'] * 100 : 0;
                @endphp
                <li class="py-4 first:pt-0 last:pb-0">
                    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <x-badge tone="primary" class="w-11 justify-center">{{ $prodi['label'] }}</x-badge>
                            <a href="{{ route('prodi.show', $prodi['id']) }}" class="truncate text-sm font-semibold text-slate-900 hover:text-primary-700 dark:text-white dark:hover:text-primary-300">{{ $prodi['nama_prodi'] }}</a>
                        </div>
                        <p class="text-sm tabular-nums text-slate-600 dark:text-slate-300">
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $prodi['mahasiswas_count'] }}</span> mahasiswa
                            <span class="text-slate-400">·</span> {{ number_format($persen, 1, ',', '.') }}%
                        </p>
                    </div>

                    <div class="mt-2.5 h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                        role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($persen, 1) }}"
                        aria-label="{{ $prodi['nama_prodi'] }}: {{ $prodi['mahasiswas_count'] }} dari {{ $totalMahasiswa }} mahasiswa, {{ $prodi['laki_count'] }} laki-laki dan {{ $prodi['perempuan_count'] }} perempuan">
                        <div class="flex h-full overflow-hidden rounded-full transition-[width] duration-700" style="width: {{ $persen }}%">
                            <span class="h-full bg-primary-600 dark:bg-primary-400" style="width: {{ $persenL }}%"></span>
                            <span class="h-full flex-1 bg-secondary-500"></span>
                        </div>
                    </div>

                    <div class="mt-1.5 flex flex-wrap justify-between gap-x-4 text-xs text-slate-500 dark:text-slate-400">
                        <span>L {{ $prodi['laki_count'] }} · P {{ $prodi['perempuan_count'] }}</span>
                        <span>{{ $prodi['kaprodi'] ? 'Kaprodi: '.$prodi['kaprodi'] : 'Kaprodi belum diisi' }}</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </x-card>


</x-app-layout>
