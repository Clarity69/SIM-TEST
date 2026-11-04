<x-app-layout title="Pengguna" :breadcrumbs="['Pengguna' => null]">
    <x-page-header title="Pengguna" description="Atur siapa yang menjadi admin atau operator. Akun yang mendaftar sendiri otomatis menjadi operator." />

    <div class="grid gap-3 sm:grid-cols-2">
        <div class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-900">
            <x-badge tone="primary" class="h-fit">Admin</x-badge>
            <p class="text-slate-600 dark:text-slate-300">Semua akses: kelola pengguna, hapus prodi, Sampah (pulihkan/hapus permanen), menerima notifikasi perubahan data.</p>
        </div>
        <div class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-900">
            <x-badge tone="neutral" class="h-fit">Operator</x-badge>
            <p class="text-slate-600 dark:text-slate-300">CRUD mahasiswa, import/export, aksi massal, tambah/ubah prodi. Tidak bisa menghapus prodi atau membuka Sampah.</p>
        </div>
    </div>

    <section class="card" aria-label="Daftar pengguna">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th scope="col" class="table-th">Pengguna</th>
                        <th scope="col" class="table-th">Status email</th>
                        <th scope="col" class="table-th">Bergabung</th>
                        <th scope="col" class="table-th">Role</th>
                        <th scope="col" class="table-th text-right"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach ($users as $user)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <x-avatar :name="$user->name" size="sm" />
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-900 dark:text-white">{{ $user->name }} @if ($user->is(auth()->user()))<span class="text-xs font-normal text-slate-500">(Anda)</span>@endif</p>
                                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="table-td">
                                @if ($user->email_verified_at)
                                    <x-badge tone="success">Terverifikasi</x-badge>
                                @else
                                    <x-badge tone="warning">Belum verifikasi</x-badge>
                                @endif
                            </td>
                            <td class="table-td whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $user->created_at?->locale('id')->translatedFormat('d M Y') }}</td>
                            <td class="table-td">
                                <form method="POST" action="{{ route('users.role', $user) }}" x-data x-ref="form">
                                    @csrf
                                    @method('PATCH')
                                    <label for="role-{{ $user->id }}" class="sr-only">Role {{ $user->name }}</label>
                                    <select id="role-{{ $user->id }}" name="role" class="form-control w-36 py-1.5" x-on:change="$refs.form.submit()" @disabled($user->is(auth()->user()))>
                                        @foreach (\App\Models\User::ROLES as $nilai => $label)
                                            <option value="{{ $nilai }}" @selected($user->role === $nilai)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <noscript><x-button type="submit" size="sm" variant="secondary">Simpan</x-button></noscript>
                                </form>
                            </td>
                            <td class="table-td text-right">
                                @unless ($user->is(auth()->user()))
                                    <x-button variant="ghost" size="icon" class="text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10" title="Hapus akun"
                                        x-on:click="$store.confirm.ask({{ \Illuminate\Support\Js::from(['title' => 'Hapus akun?', 'message' => 'Akun '.$user->name.' ('.$user->email.') akan dihapus permanen.', 'action' => route('users.destroy', $user)]) }})">
                                        <x-icon name="trash-2" class="size-4" /><span class="sr-only">Hapus {{ $user->name }}</span>
                                    </x-button>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{{ $users->links() }}</div>
        @endif
    </section>
</x-app-layout>
