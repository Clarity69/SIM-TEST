<x-app-layout title="Edit {{ $prodi->nama_prodi }}" :breadcrumbs="['Program Studi' => route('prodi.index'), $prodi->nama_prodi => route('prodi.show', $prodi), 'Edit' => null]">
    <x-page-header title="Edit Program Studi" description="Perbarui data {{ $prodi->nama_prodi }}." />
    @include('prodi._form', ['action' => route('prodi.update', $prodi)])
</x-app-layout>
