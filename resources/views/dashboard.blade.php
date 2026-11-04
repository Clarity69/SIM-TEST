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
    <x-page-header title="Dashboard" description="Ringkasan data akademik {{ config('stitek.singkatan') }} · diperbarui otomatis setiap 5 menit">
        <x-button variant="secondary" icon="printer" :href="route('mahasiswa.pdf')" target="_blank">Cetak PDF</x-button>
        <x-button icon="plus" :href="route('mahasiswa.create')">Tambah Mahasiswa</x-button>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Total Mahasiswa" :value="$totalMahasiswa" icon="users" :trend="$tren['total']" :delta="$selisih['total']" />
        <x-stat-card label="Laki-laki" :value="$totalLakiLaki" icon="user" tone="accent" :trend="$tren['laki']" :delta="$selisih['laki']" />
        <x-stat-card label="Perempuan" :value="$totalPerempuan" icon="user-round" tone="secondary" :trend="$tren['perempuan']" :delta="$selisih['perempuan']" />
        <x-stat-card label="Program Studi" :value="$totalProdi" icon="graduation-cap" tone="success" :trend="$tren['prodi']" :delta="$selisih['prodi']" />
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
