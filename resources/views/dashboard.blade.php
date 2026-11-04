<x-app-layout title="Dashboard" :breadcrumbs="['Dashboard' => null]">
    @php
        $grafikProdi = [
            'type' => 'bar',
            'labels' => $mahasiswaPerProdi->pluck('label'),
            'names' => $mahasiswaPerProdi->pluck('nama_prodi'),
            'values' => $mahasiswaPerProdi->pluck('mahasiswas_count'),
        ];
        $grafikGender = ['type' => 'doughnut', 'labels' => ['Laki-laki', 'Perempuan'], 'values' => [$totalLakiLaki, $totalPerempuan]];
    @endphp
    <x-page-header title="Dashboard" description="Ringkasan data akademik {{ config('stitek.singkatan') }} · persentase dibanding data sebelum perubahan terakhir">
        <x-button variant="secondary" icon="printer" :href="route('mahasiswa.pdf')" target="_blank">Cetak PDF</x-button>
        <x-button icon="plus" :href="route('mahasiswa.create')">Tambah Mahasiswa</x-button>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Total Mahasiswa" :value="$totalMahasiswa" icon="users" :trend="$tren['total']" :delta="$selisih['total']" :since="$terakhir['total']" />
        <x-stat-card label="Laki-laki" :value="$totalLakiLaki" icon="user" tone="accent" :trend="$tren['laki']" :delta="$selisih['laki']" :since="$terakhir['laki']" />
        <x-stat-card label="Perempuan" :value="$totalPerempuan" icon="user-round" tone="secondary" :trend="$tren['perempuan']" :delta="$selisih['perempuan']" :since="$terakhir['perempuan']" />
        <x-stat-card label="Program Studi" :value="$totalProdi" icon="graduation-cap" tone="success" :trend="$tren['prodi']" :delta="$selisih['prodi']" :since="$terakhir['prodi']" />
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <x-card title="Mahasiswa per Program Studi" description="Jumlah mahasiswa terdaftar di setiap prodi" class="lg:col-span-2">
            @if ($totalMahasiswa > 0)
                <div class="h-72">
                    <canvas role="img" aria-label="Grafik batang jumlah mahasiswa per program studi"
                        data-chart="{{ json_encode($grafikProdi) }}"></canvas>
                </div>
                <ul class="sr-only">
                    @foreach ($mahasiswaPerProdi as $prodi)
                        <li>{{ $prodi['nama_prodi'] }}: {{ $prodi['mahasiswas_count'] }} mahasiswa</li>
                    @endforeach
                </ul>
            @else
                <x-empty-state icon="graduation-cap" title="Belum ada data" description="Grafik muncul setelah data mahasiswa ditambahkan." />
            @endif
        </x-card>

        <x-card title="Komposisi Gender" description="Perbandingan laki-laki dan perempuan">
            @if ($totalMahasiswa > 0)
                <div class="relative mx-auto h-48 w-48">
                    <canvas role="img" aria-label="Diagram donat komposisi gender"
                        data-chart="{{ json_encode($grafikGender) }}"></canvas>
                    <div class="pointer-events-none absolute inset-0 grid place-items-center text-center">
                        <div>
                            <p class="text-2xl font-bold tabular-nums text-slate-900 dark:text-white">{{ $totalMahasiswa }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">mahasiswa</p>
                        </div>
                    </div>
                </div>
                <dl class="mt-6 space-y-3 text-sm">
                    @foreach (['Laki-laki' => [$totalLakiLaki, 'bg-primary-600 dark:bg-primary-400'], 'Perempuan' => [$totalPerempuan, 'bg-secondary-500']] as $jk => [$jumlah, $warna])
                        <div class="flex items-center gap-3">
                            <span class="size-2.5 rounded-sm {{ $warna }}" aria-hidden="true"></span>
                            <dt class="flex-1 text-slate-600 dark:text-slate-300">{{ $jk }}</dt>
                            <dd class="font-semibold tabular-nums text-slate-900 dark:text-white">{{ $jumlah }} · {{ round($jumlah / $totalMahasiswa * 100) }}%</dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <x-empty-state icon="users" title="Belum ada data" />
            @endif
        </x-card>
    </div>

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

    <x-card title="Mahasiswa Terbaru" description="5 data terakhir yang ditambahkan" :padding="false">
        <x-slot:actions>
            <a href="{{ route('mahasiswa.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-700 hover:text-primary-800 dark:text-primary-300">
                Lihat semua <x-icon name="arrow-up-right" class="size-4" />
            </a>
        </x-slot:actions>

        @if ($mahasiswaTerbaru->isEmpty())
            <x-empty-state title="Belum ada mahasiswa" description="Tambahkan mahasiswa pertama untuk mulai mengisi dashboard.">
                <x-button icon="plus" :href="route('mahasiswa.create')">Tambah Mahasiswa</x-button>
            </x-empty-state>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th scope="col" class="table-th">NIM</th>
                            <th scope="col" class="table-th">Nama</th>
                            <th scope="col" class="table-th">Program Studi</th>
                            <th scope="col" class="table-th">Jenis Kelamin</th>
                            <th scope="col" class="table-th">Ditambahkan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($mahasiswaTerbaru as $mahasiswa)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="table-td font-medium tabular-nums">{{ $mahasiswa['nim'] }}</td>
                                <td class="table-td">
                                    <a href="{{ route('mahasiswa.show', $mahasiswa['id']) }}" class="flex items-center gap-3 font-medium text-slate-900 hover:text-primary-700 dark:text-white dark:hover:text-primary-300">
                                        <x-avatar :name="$mahasiswa['nama']" :jenis-kelamin="$mahasiswa['jenis_kelamin']" size="sm" />
                                        {{ $mahasiswa['nama'] }}
                                    </a>
                                </td>
                                <td class="table-td text-slate-600 dark:text-slate-300">{{ $mahasiswa['prodi'] }}</td>
                                <td class="table-td"><x-badge :tone="$mahasiswa['jenis_kelamin'] === 'Laki-laki' ? 'primary' : 'secondary'">{{ $mahasiswa['jenis_kelamin'] }}</x-badge></td>
                                <td class="table-td whitespace-nowrap text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Carbon::parse($mahasiswa['created_at'])->locale('id')->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</x-app-layout>
