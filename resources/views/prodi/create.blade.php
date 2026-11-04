<x-app-layout title="Tambah Program Studi" :breadcrumbs="['Program Studi' => route('prodi.index'), 'Tambah' => null]">
    <x-page-header title="Tambah Program Studi" description="Kolom bertanda * wajib diisi." />
    @include('prodi._form', ['action' => route('prodi.store')])
</x-app-layout>
