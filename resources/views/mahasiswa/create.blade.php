<x-app-layout title="Tambah Mahasiswa" :breadcrumbs="['Data Mahasiswa' => route('mahasiswa.index'), 'Tambah' => null]">
    <x-page-header title="Tambah Mahasiswa" eyebrow="Data akademik" description="Lengkapi identitas, kontak, dan informasi akademik. Kolom bertanda * wajib diisi.">
        <x-button variant="ghost" icon="arrow-left" :href="route('mahasiswa.index')">Kembali ke daftar</x-button>
    </x-page-header>
    @include('mahasiswa._form', ['action' => route('mahasiswa.store')])
</x-app-layout>
