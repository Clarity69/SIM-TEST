<x-app-layout title="Tambah Mahasiswa" :breadcrumbs="['Data Mahasiswa' => route('mahasiswa.index'), 'Tambah' => null]">
    <x-page-header title="Tambah Mahasiswa" description="Kolom bertanda * wajib diisi." />
    @include('mahasiswa._form', ['action' => route('mahasiswa.store')])
</x-app-layout>
